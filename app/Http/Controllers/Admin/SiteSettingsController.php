<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::get();
        return view('admin.site-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validationRules = [
            'site_name' => ['required','string','max:100'],
            'site_tagline' => ['nullable','string','max:150'],
            'logo_url' => ['nullable','string','max:255'],
            'favicon_url' => ['nullable','string','max:255'],
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string','max:500'],
            'meta_keywords' => ['nullable','string','max:500'],
            'footer_text' => ['nullable','string','max:500'],
            'privacy_url' => ['nullable','string','max:255'],
            'terms_url' => ['nullable','string','max:255'],
            'cookies_url' => ['nullable','string','max:255'],
            'help_center_url' => ['nullable','string','max:255'],
            'shipping_info_url' => ['nullable','string','max:255'],
            'returns_url' => ['nullable','string','max:255'],
            'contact_us_url' => ['nullable','string','max:255'],
            'wishlist_enabled' => ['nullable','boolean'],
            'reviews_enabled' => ['nullable','boolean'],
            'reviews_require_purchase' => ['nullable','boolean'],
            'reviews_require_approval' => ['nullable','boolean'],
            'reviews_allow_anonymous' => ['nullable','boolean'],
            'newsletter_enabled' => ['nullable','boolean'],
            'newsletter_double_opt_in' => ['nullable','boolean'],
            'newsletter_send_welcome_email' => ['nullable','boolean'],
            'social_facebook' => ['nullable','string','max:255'],
            'social_twitter' => ['nullable','string','max:255'],
            'social_instagram' => ['nullable','string','max:255'],
            'social_linkedin' => ['nullable','string','max:255'],
            'product_display_columns_mobile' => ['nullable','integer','min:1','max:4'],
            'product_display_columns_desktop' => ['nullable','integer','min:1','max:6'],
            'schema_enabled' => ['nullable','boolean'],
            'schema_organization_name' => ['nullable','string','max:255'],
            'schema_organization_logo' => ['nullable','string','max:500'],
            'schema_organization_phone' => ['nullable','string','max:50'],
            'schema_organization_email' => ['nullable','email','max:255'],
            'schema_organization_address' => ['nullable','string','max:500'],
            'schema_organization_type' => ['nullable','string','max:100'],
            'sitemap_enabled' => ['nullable','boolean'],
            'sitemap_priority_home' => ['nullable','integer','min:1','max:10'],
            'sitemap_priority_product' => ['nullable','integer','min:1','max:10'],
            'sitemap_priority_category' => ['nullable','integer','min:1','max:10'],
            'sitemap_priority_page' => ['nullable','integer','min:1','max:10'],
            'sitemap_change_frequency' => ['nullable','string','in:always,hourly,daily,weekly,monthly,yearly,never'],
            'google_analytics_code' => ['nullable','string'],
            'facebook_pixel_code' => ['nullable','string'],
            'microsoft_clarity_code' => ['nullable','string'],
            'custom_head_code' => ['nullable','string'],
            'custom_body_code' => ['nullable','string'],
        ];
        
        // Only validate theme if column exists
        if (Schema::hasColumn('site_settings', 'theme')) {
            $validationRules['theme'] = ['required','string','in:theme1,theme2,theme3,pure-rose'];
        }
        
        // Only validate license_key if column exists
        if (Schema::hasColumn('site_settings', 'license_key')) {
            $validationRules['license_key'] = ['nullable','string','max:255'];
        }
        
        $data = $request->validate($validationRules);
        $data['wishlist_enabled'] = (bool) ($request->input('wishlist_enabled') ?? false);
        $data['reviews_enabled'] = (bool) ($request->input('reviews_enabled') ?? false);
        $data['reviews_require_purchase'] = (bool) ($request->input('reviews_require_purchase') ?? false);
        $data['reviews_require_approval'] = (bool) ($request->input('reviews_require_approval') ?? false);
        $data['reviews_allow_anonymous'] = (bool) ($request->input('reviews_allow_anonymous') ?? false);
        $data['newsletter_enabled'] = (bool) ($request->input('newsletter_enabled') ?? false);
        $data['newsletter_double_opt_in'] = (bool) ($request->input('newsletter_double_opt_in') ?? false);
        $data['newsletter_send_welcome_email'] = (bool) ($request->input('newsletter_send_welcome_email') ?? false);
        $data['product_display_columns_mobile'] = (int) ($request->input('product_display_columns_mobile') ?? 2);
        $data['product_display_columns_desktop'] = (int) ($request->input('product_display_columns_desktop') ?? 3);
        $data['schema_enabled'] = (bool) ($request->input('schema_enabled') ?? true);
        $data['sitemap_enabled'] = (bool) ($request->input('sitemap_enabled') ?? true);
        $data['sitemap_priority_home'] = (int) ($request->input('sitemap_priority_home') ?? 10);
        $data['sitemap_priority_product'] = (int) ($request->input('sitemap_priority_product') ?? 8);
        $data['sitemap_priority_category'] = (int) ($request->input('sitemap_priority_category') ?? 7);
        $data['sitemap_priority_page'] = (int) ($request->input('sitemap_priority_page') ?? 6);
        $data['sitemap_change_frequency'] = $request->input('sitemap_change_frequency') ?? 'weekly';
        
        // Handle theme field - only include if column exists
        if (Schema::hasColumn('site_settings', 'theme') && $request->has('theme')) {
            $data['theme'] = $request->input('theme', 'theme1');
        }
        
        // Handle license_key field - only include if column exists
        if (Schema::hasColumn('site_settings', 'license_key') && $request->has('license_key')) {
            $data['license_key'] = $request->input('license_key');
        }
        
        $settings = SiteSetting::get();
        $settings->update($data);
        $tab = in_array($request->input('active_tab'), ['general','features','seo','theme','tracking'], true)
            ? $request->input('active_tab') : 'general';
        return redirect()->route('admin.site-settings.index', ['tab' => $tab])->with('success','Site settings updated.');
    }
}


