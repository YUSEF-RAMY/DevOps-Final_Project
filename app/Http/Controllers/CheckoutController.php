<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderConfirmation;
use Illuminate\Support\Str;
use App\Http\Controllers\PaymentGateway\PaymentGatewayManager;
use App\Support\PointService;
use App\Support\ThemeHelper;
use App\Models\CoinSetting;
use App\Models\ShippingSetting;

class CheckoutController extends Controller
{
    protected function findCurrentCart(Request $request): ?Cart
    {
        $query = Cart::query()->with('items.product', 'coupon');
        $sessionId = $request->session()->get('cart_session_id');
        if (auth()->check()) {
            $cart = (clone $query)->where('user_id', auth()->id())->first();
            if ($cart) {
                return $cart;
            }
        }
        if ($sessionId) {
            return (clone $query)->where('session_id', $sessionId)->first();
        }
        return null;
    }

    public function show(Request $request)
    {
        $cart = $this->findCurrentCart($request);

        if (!$cart) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Block showing checkout if any item is out of stock/inactive or exceeds stock
        foreach ($cart->items as $ci) {
            if (!$ci->product || !$ci->product->is_active || (int) $ci->product->stock <= 0) {
                return redirect()->route('cart.index')->with('error', 'Some items are out of stock and have been moved to the Unavailable section. Please remove them to continue.');
            }
            if ($ci->quantity > (int) $ci->product->stock) {
                return redirect()->route('cart.index')->with('error', 'Some item quantities exceed stock. Please adjust to continue.');
            }
        }

        $gatewayManager = new PaymentGatewayManager();
        $enabledGateways = $gatewayManager->getEnabledGateways();
        
        // Get sandbox mode status for each gateway
        $gatewaySandboxModes = [];
        foreach ($enabledGateways as $name => $gateway) {
            $gatewaySandboxModes[$name] = $gateway['config']['sandbox_mode'] ?? true;
        }

        // Compute shipping and tax based on Bangladeshi settings
        $shippingSettings = ShippingSetting::get();
        $shipping = 0.0;
        $tax = 0.0;
        $taxableAmount = $cart->subtotal - $cart->discount_total;
        
        if ($shippingSettings->enabled) {
            // Free shipping check should use taxable amount (subtotal - discount)
            if ($shippingSettings->free_shipping_enabled && $taxableAmount >= (float) $shippingSettings->free_shipping_min_total) {
                $shipping = 0.0;
            } else {
                $division = trim((string) old('billing_division', ''));
                $district = trim((string) old('billing_district', ''));
                
                // Try district-based rate first
                $found = null;
                if ($district && !empty($shippingSettings->district_rates)) {
                    $districtRates = (array) ($shippingSettings->district_rates ?? []);
                    foreach ($districtRates as $conf) {
                        if (!is_array($conf)) continue;
                        $d = trim((string) ($conf['district'] ?? ''));
                        if ($d && strcasecmp($d, $district) === 0) {
                            $found = $conf;
                            break;
                        }
                    }
                }
                
                // Try division-based rate if district not found
                if (!$found && $division && !empty($shippingSettings->division_rates)) {
                    $divisionRates = (array) ($shippingSettings->division_rates ?? []);
                    foreach ($divisionRates as $conf) {
                        if (!is_array($conf)) continue;
                        $div = trim((string) ($conf['division'] ?? ''));
                        if ($div && strcasecmp($div, $division) === 0) {
                            $found = $conf;
                            break;
                        }
                    }
                }
                
                if ($found) {
                    $type = $found['type'] ?? 'flat';
                    $amount = (float) ($found['amount'] ?? 0);
                    if ($type === 'percent') {
                        $shipping = round($taxableAmount * ($amount/100), 2);
                    } else {
                        $shipping = $amount;
                    }
                } else if ($shippingSettings->flat_rate > 0) {
                    $shipping = (float) $shippingSettings->flat_rate;
                }
            }
        }
        
        // Calculate tax
        if ($shippingSettings && $shippingSettings->tax_enabled && $shippingSettings->tax_rate > 0) {
            if ($shippingSettings->tax_type === 'percent') {
                $tax = round($taxableAmount * ($shippingSettings->tax_rate / 100), 2);
            } else {
                $tax = round((float) $shippingSettings->tax_rate, 2);
            }
        }

        // Load districts and divisions for dropdowns
        $divisions = ['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Barisal', 'Sylhet', 'Rangpur', 'Mymensingh'];
        $districts = \App\Models\District::where('is_active', true)
            ->orderBy('division')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Load user's saved addresses if authenticated - prioritize saved addresses
        $defaultBillingAddress = null;
        $defaultShippingAddress = null;
        $hasAnyAddress = false;
        
        if (auth()->check()) {
            // Check if user has any saved addresses
            $hasAnyAddress = \App\Models\UserAddress::where('user_id', auth()->id())->exists();
            
            if ($hasAnyAddress) {
                // Get default billing address, or most recent billing address
                $defaultBillingAddress = \App\Models\UserAddress::where('user_id', auth()->id())
                    ->where('type', 'billing')
                    ->orderByDesc('is_default')
                    ->orderByDesc('created_at')
                    ->first();
                
                // If no billing address, try to use any address as fallback
                if (!$defaultBillingAddress) {
                    $defaultBillingAddress = \App\Models\UserAddress::where('user_id', auth()->id())
                        ->orderByDesc('is_default')
                        ->orderByDesc('created_at')
                        ->first();
                }
                
                // Get default shipping address, or most recent shipping address
                $defaultShippingAddress = \App\Models\UserAddress::where('user_id', auth()->id())
                    ->where('type', 'shipping')
                    ->orderByDesc('is_default')
                    ->orderByDesc('created_at')
                    ->first();
                
                // If no shipping address, try to use any address as fallback
                if (!$defaultShippingAddress) {
                    $defaultShippingAddress = \App\Models\UserAddress::where('user_id', auth()->id())
                        ->orderByDesc('is_default')
                        ->orderByDesc('created_at')
                        ->first();
                }
            }
        }

        return view(ThemeHelper::view('checkout.show'), [
            'cart' => $cart,
            'gateways' => $enabledGateways,
            'gatewaySandboxModes' => $gatewaySandboxModes,
            'shipping' => $shipping,
            'divisions' => $divisions,
            'districts' => $districts,
            'defaultBillingAddress' => $defaultBillingAddress,
            'defaultShippingAddress' => $defaultShippingAddress,
            'hasAnyAddress' => $hasAnyAddress,
        ]);
    }

    public function calculateShippingTax(Request $request)
    {
        $cart = $this->findCurrentCart($request);
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        // Get division and district from request
        $division = trim((string) $request->input('division', ''));
        $district = trim((string) $request->input('district', ''));
        
        // If district is provided but division is not, try to get division from district
        if (!$division && $district) {
            $districtModel = \App\Models\District::where('name', $district)
                ->where('is_active', true)
                ->first();
            if ($districtModel) {
                $division = $districtModel->division;
            }
        }
        
        $shippingSettings = ShippingSetting::get();
        $shipping = 0.0;
        $tax = 0.0;
        
        // Calculate taxable amount (subtotal - discount) for shipping and tax calculations
        $taxableAmount = $cart->subtotal - $cart->discount_total;
        
        // Calculate shipping
        if ($shippingSettings && $shippingSettings->enabled) {
            // Free shipping check should use taxable amount (subtotal - discount)
            if ($shippingSettings->free_shipping_enabled && $taxableAmount >= (float) $shippingSettings->free_shipping_min_total) {
                $shipping = 0.0;
            } else {
                // Try district-based rate first
                $found = null;
                if ($district && !empty($shippingSettings->district_rates)) {
                    $districtRates = (array) ($shippingSettings->district_rates ?? []);
                    foreach ($districtRates as $conf) {
                        if (!is_array($conf)) continue;
                        $d = trim((string) ($conf['district'] ?? ''));
                        if ($d && strcasecmp($d, $district) === 0) {
                            $found = $conf;
                            break;
                        }
                    }
                }
                
                // Try division-based rate if district not found
                if (!$found && $division && !empty($shippingSettings->division_rates)) {
                    $divisionRates = (array) ($shippingSettings->division_rates ?? []);
                    foreach ($divisionRates as $conf) {
                        if (!is_array($conf)) continue;
                        $div = trim((string) ($conf['division'] ?? ''));
                        if ($div && strcasecmp($div, $division) === 0) {
                            $found = $conf;
                            break;
                        }
                    }
                }
                
                if ($found) {
                    $type = $found['type'] ?? 'flat';
                    $amount = (float) ($found['amount'] ?? 0);
                    if ($type === 'percent') {
                        // Use taxable amount for percentage-based shipping
                        $shipping = round($taxableAmount * ($amount/100), 2);
                    } else {
                        $shipping = $amount;
                    }
                } else if ($shippingSettings->flat_rate > 0) {
                    $shipping = (float) $shippingSettings->flat_rate;
                }
            }
        }
        
        // Calculate tax
        $tax = 0.0;
        if ($shippingSettings && $shippingSettings->tax_enabled && $shippingSettings->tax_rate > 0) {
            if ($shippingSettings->tax_type === 'percent') {
                $tax = round($taxableAmount * ($shippingSettings->tax_rate / 100), 2);
            } else {
                $tax = round((float) $shippingSettings->tax_rate, 2);
            }
        }
        
        $total = $cart->subtotal - $cart->discount_total + $tax + $shipping;
        
        return response()->json([
            'success' => true,
            'shipping' => (float) $shipping,
            'tax' => (float) $tax,
            'subtotal' => (float) $cart->subtotal,
            'discount' => (float) $cart->discount_total,
            'total' => (float) $total,
            'currency' => $cart->currency ?? 'BDT',
            'debug' => [
                'division' => $division,
                'district' => $district,
                'shipping_enabled' => $shippingSettings ? $shippingSettings->enabled : false,
                'tax_enabled' => $shippingSettings ? $shippingSettings->tax_enabled : false,
            ]
        ]);
    }

    public function place(Request $request)
    {
        $request->validate([
            'billing_name' => ['required', 'string', 'max:255'],
            'billing_email' => ['required', 'email'],
            'billing_phone' => ['required', 'string', 'max:20'],
            'billing_division' => ['nullable', 'string', 'max:255'],
            'billing_district' => ['nullable', 'string', 'max:255'],
            'gateway' => ['required', 'in:bkash,nagad,rocket,ssl_commerce,cod,stripe,paypal'],
        ], [
            'billing_name.required' => 'Please enter your full name.',
            'billing_email.required' => 'Please enter your email address.',
            'billing_email.email' => 'Please enter a valid email address.',
            'billing_phone.required' => 'Please enter your phone number.',
            'gateway.required' => 'Please select a payment method.',
            'gateway.in' => 'Please select a valid payment method.',
        ]);
        $cart = $this->findCurrentCart($request);
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        
        // Validate coupon if one is applied
        if ($cart->coupon_id) {
            // Ensure cart items are loaded for validation
            $cart->load('items.product');
            
            $coupon = \App\Models\Coupon::find($cart->coupon_id);
            
            if (!$coupon) {
                // Coupon no longer exists - remove it from cart
                $cart->removeCoupon();
                return redirect()->route('checkout.show')->with('error', 'The coupon code is no longer valid and has been removed from your cart.');
            }
            
            // Check if coupon is still valid
            if (!$coupon->isValid()) {
                $cart->removeCoupon();
                return redirect()->route('checkout.show')->with('error', 'The coupon code has expired or is no longer valid and has been removed from your cart.');
            }
            
            // Check if coupon can still be used by this user
            if (!$coupon->canBeUsedBy($cart->user, $cart->session_id)) {
                $cart->removeCoupon();
                return redirect()->route('checkout.show')->with('error', 'You have reached the usage limit for this coupon. It has been removed from your cart.');
            }
            
            // Check if coupon still applies to cart
            if (!$coupon->appliesToCart($cart)) {
                $cart->removeCoupon();
                return redirect()->route('checkout.show')->with('error', 'This coupon is no longer applicable to your cart items and has been removed.');
            }
            
            // Recalculate discount to ensure it's still correct (in case coupon value changed)
            $newDiscount = $coupon->calculateDiscount($cart->subtotal);
            if (abs($cart->coupon_discount - $newDiscount) > 0.01) {
                // Discount amount changed, update it
                $cart->coupon_discount = $newDiscount;
                $cart->recalculateTotals();
                $cart->refresh(); // Refresh to get updated totals
            }
        }
        
        // Block checkout if any item is out of stock or inactive
        foreach ($cart->items as $ci) {
            if (!$ci->product || !$ci->product->is_active || (int) $ci->product->stock <= 0) {
                return redirect()->route('cart.index')->with('error', 'Some items are out of stock and have been disabled. Please remove them to continue.');
            }
            if ($ci->quantity > (int) $ci->product->stock) {
                return redirect()->route('cart.index')->with('error', 'Some item quantities exceed stock. Please adjust to continue.');
            }
        }

		$order = new Order();
		$order->fill($request->only([
			'billing_name', 'billing_email', 'billing_phone', 'billing_address', 'billing_postcode', 'billing_country',
			'billing_division', 'billing_district', 'billing_upazila',
			'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_postcode', 'shipping_country',
			'shipping_division', 'shipping_district', 'shipping_upazila',
		]));
        $order->number = strtoupper(Str::random(10));
		$order->user_id = auth()->id();
		$order->status = 'pending';
		$order->subtotal = $cart->subtotal;
		$order->discount_total = $cart->discount_total;
        
        // Recompute shipping and tax on submission using Bangladeshi logic
        $shippingSettings = ShippingSetting::get();
        $shipping = 0.0;
        $tax = 0.0;
        $taxableAmount = $cart->subtotal - $cart->discount_total;
        
        if ($shippingSettings->enabled) {
            // Free shipping check should use taxable amount (subtotal - discount)
            if ($shippingSettings->free_shipping_enabled && $taxableAmount >= (float) $shippingSettings->free_shipping_min_total) {
                $shipping = 0.0;
            } else {
                $division = trim((string) $request->string('billing_division') ?: '');
                $district = trim((string) $request->string('billing_district') ?: '');
                
                // Try district-based rate first
                $found = null;
                if ($district && !empty($shippingSettings->district_rates)) {
                    $districtRates = (array) ($shippingSettings->district_rates ?? []);
                    foreach ($districtRates as $conf) {
                        if (!is_array($conf)) continue;
                        $d = trim((string) ($conf['district'] ?? ''));
                        if ($d && strcasecmp($d, $district) === 0) {
                            $found = $conf;
                            break;
                        }
                    }
                }
                
                // Try division-based rate if district not found
                if (!$found && $division && !empty($shippingSettings->division_rates)) {
                    $divisionRates = (array) ($shippingSettings->division_rates ?? []);
                    foreach ($divisionRates as $conf) {
                        if (!is_array($conf)) continue;
                        $div = trim((string) ($conf['division'] ?? ''));
                        if ($div && strcasecmp($div, $division) === 0) {
                            $found = $conf;
                            break;
                        }
                    }
                }
                
                if ($found) {
                    $type = $found['type'] ?? 'flat';
                    $amount = (float) ($found['amount'] ?? 0);
                    if ($type === 'percent') {
                        $shipping = round($taxableAmount * ($amount/100), 2);
                    } else {
                        $shipping = $amount;
                    }
                } else if ($shippingSettings->flat_rate > 0) {
                    $shipping = (float) $shippingSettings->flat_rate;
                }
            }
        }
        
        // Calculate tax
        if ($shippingSettings && $shippingSettings->tax_enabled && $shippingSettings->tax_rate > 0) {
            if ($shippingSettings->tax_type === 'percent') {
                $tax = round($taxableAmount * ($shippingSettings->tax_rate / 100), 2);
            } else {
                $tax = round((float) $shippingSettings->tax_rate, 2);
            }
        }
        
        $order->tax_total = $tax;
        $order->shipping_total = $shipping;
        $order->grand_total = $cart->subtotal - $cart->discount_total + $tax + $shipping;
		$order->currency = 'BDT'; // Default to Bangladeshi Taka
        $order->payment_method = $request->string('gateway');
        $order->payment_status = 'unpaid';
		$order->shipping_status = 'unshipped';
		
		// Save order first to get the ID
		$order->save();
        
        // Create order items for ALL orders (both COD and payment gateways)
        // For payment gateways, items are created before redirecting to payment
        // For COD, items are created immediately
        foreach ($cart->items as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name,
                'product_sku' => $cartItem->product->sku,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'line_total' => $cartItem->line_total,
            ]);
        }

        app(\App\Services\NotificationService::class)->notifyNewOrder(
            $order->id,
            $order->number,
            (float) $order->grand_total
        );
        
        // Process payment for payment gateways
        $gatewayName = trim((string) $request->input('gateway', ''));
        $isCod = (strtolower($gatewayName) === 'cod');
        
        if (!$isCod) {
            // Payment gateway - process payment and redirect to gateway
            $gatewayManager = new PaymentGatewayManager();
            
            // Check if gateway exists in the manager
            if (!$gatewayManager->hasGateway($gatewayName)) {
                $order->payment_status = 'failed';
                $order->save();
                
                \Log::error('Payment gateway not found', [
                    'order_id' => $order->id,
                    'gateway' => $gatewayName,
                ]);
                
                return redirect()->back()
                    ->with('error', 'Selected payment method is not available. Please choose a different payment method.');
            }
            
            $gateway = $gatewayManager->getGateway($gatewayName);
            if ($gateway) {
                $paymentData = [
                    'amount' => (float) $order->grand_total,
                    'currency' => 'BDT',
                    'order_id' => $order->id,
                    'customer_name' => $order->billing_name,
                    'customer_email' => $order->billing_email,
                    'customer_phone' => $order->billing_phone,
                    'customer_address' => $order->billing_address,
                    'customer_district' => $order->billing_district,
                    'customer_division' => $order->billing_division,
                    'customer_upazila' => $order->billing_upazila,
                    'customer_postcode' => $order->billing_postcode,
                ];
                try {
                    $paymentResult = $gateway->processPayment($paymentData);
                    
                    // Check if payment processing was successful
                    if (!($paymentResult['success'] ?? false)) {
                        // Payment processing failed
                        $order->payment_status = 'failed';
                        $order->save();
                        
                        \Log::error('Payment processing failed', [
                            'order_id' => $order->id,
                            'gateway' => $gatewayName,
                            'error' => $paymentResult['error'] ?? 'Unknown error',
                        ]);
                        
                        return redirect()->back()
                            ->with('error', 'Payment processing failed: ' . ($paymentResult['error'] ?? 'Unknown error. Please try again or contact support.'));
                    }
                    
                    // Payment processing successful - save transaction details
                    $order->payment_transaction_id = $paymentResult['transaction_id'] ?? $paymentResult['payment_id'] ?? null;
                    $order->payment_transaction_details = json_encode($paymentResult);
                    $order->save();
                    
                    // Check if gateway returns redirect URL
                    if (empty($paymentResult['redirect_url'])) {
                        // No redirect URL - this shouldn't happen for payment gateways
                        $order->payment_status = 'failed';
                        $order->save();
                        
                        \Log::error('Payment gateway did not return redirect URL', [
                            'order_id' => $order->id,
                            'gateway' => $gatewayName,
                            'payment_result' => $paymentResult,
                        ]);
                        
                        return redirect()->back()
                            ->with('error', 'Payment gateway error: No redirect URL received. Please try again or contact support.');
                    }
                    
                    // Redirect to payment gateway
                    // For SSL Commerce, we need to POST form data
                    if ($gatewayName === 'ssl_commerce' && !empty($paymentResult['post_data'])) {
                        return view(ThemeHelper::view('payment.redirect'), [
                            'gateway' => $gatewayName,
                            'redirect_url' => $paymentResult['redirect_url'],
                            'post_data' => $paymentResult['post_data'],
                            'order_id' => $order->id,
                        ]);
                    }
                    
                    // For bKash, Nagad, Rocket - redirect directly to gateway's official payment page
                    if (in_array($gatewayName, ['bkash', 'nagad', 'rocket'])) {
                        return redirect($paymentResult['redirect_url']);
                    }
                    
                    // For other gateways with direct redirect (PayPal, Stripe, etc.)
                    return redirect($paymentResult['redirect_url']);
                    
                } catch (\Exception $e) {
                    // Payment processing exception
                    $order->payment_status = 'failed';
                    $order->save();
                    
                    \Log::error('Payment processing exception', [
                        'order_id' => $order->id,
                        'gateway' => $gatewayName,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    return redirect()->back()
                        ->with('error', 'Payment processing failed: ' . $e->getMessage() . '. Please try again or contact support.');
                }
            } else {
                // Gateway class not found (shouldn't happen if hasGateway returned true)
                $order->payment_status = 'failed';
                $order->save();
                
                \Log::error('Payment gateway class not found', [
                    'order_id' => $order->id,
                    'gateway' => $gatewayName,
                ]);
                
                return redirect()->back()
                    ->with('error', 'Payment gateway error. Please try again or contact support.');
            }
        } else {
            // COD - No payment processing needed, clear cart and complete order
            // Order items already created above
            
            // Clear cart
            $cart->items()->delete();
            $cart->delete();
            $request->session()->forget('cart_session_id');
            
            // Mark order as pending for COD (payment will be collected on delivery)
            $order->payment_status = 'pending';
            $order->save();

            // Send order confirmation email
		if ($order->user) {
			$order->user->notify(new OrderConfirmation($order));
			try {
				$ratePer = 100; $rateCoins = 1; $minAward = 1; $codBonus = 0; $enableOrder = true; $enableCod = true; $enableAll = true;
				try {
					$cs = CoinSetting::get();
					$enableAll = (bool) ($cs->coins_enabled ?? true);
					$enableOrder = (bool) ($cs->order_award_enabled ?? true);
					$enableCod = (bool) ($cs->cod_bonus_enabled ?? true);
					$ratePer = (int) max(1, (int) ($cs->order_rate_per ?? 100));
					$rateCoins = (int) max(0, (int) ($cs->order_rate_coins ?? 1));
					$minAward = (int) max(0, (int) ($cs->order_min_award ?? 1));
					$codBonus = (int) max(0, (int) ($cs->cod_bonus ?? 0));
				} catch (\Throwable $e) {}
				if ($enableAll && $enableOrder) {
					$steps = (int) floor(((float) $order->grand_total) / $ratePer);
					$award = max($minAward, $steps * $rateCoins);
					if ($award > 0) {
						PointService::award($order->user, $award, 'order_place', 'Order placed', $order, ['order_id' => $order->id]);
					}
				}
				if ($enableAll && $enableCod && $order->payment_method === 'cod' && $codBonus > 0) {
					// Check if COD is enabled as a payment method
					$codEnabled = \App\Models\PaymentGatewaySetting::where('gateway', 'cod')
						->where('key', 'enabled')
						->value('value');
					if ((bool) ($codEnabled ?? true)) {
						PointService::award($order->user, $codBonus, 'cod_choice', 'Chose COD', $order);
					}
				}
			} catch (\Throwable $e) {
				// swallow
			}
		}

            // For guests, send a signed link to view the order; for users, redirect to their orders
            if (auth()->check()) {
                return redirect()->route('orders.show', $order->id)->with('success', 'Order placed');
            }
            return redirect()->signedRoute('orders.guest.show', ['order' => $order->id], now()->addDays(15))
                ->with('success', 'Order placed');
        }
    }
}
