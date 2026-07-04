<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FloristAiService
{
    public function consult(string $message, ?int $userId = null): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderByDesc('is_featured')
            ->limit(12)
            ->get(['id', 'name', 'slug', 'price', 'stock', 'short_description']);

        $catalogContext = $products->map(fn (Product $p) => sprintf(
            'ID:%d | %s | AED %s | Stock:%d | %s',
            $p->id,
            $p->name,
            number_format((float) $p->price, 2),
            $p->stock,
            Str::limit($p->short_description ?? '', 80)
        ))->implode("\n");

        $systemPrompt = <<<PROMPT
You are the PURE ROSE Floral Assistant — an elegant, warm luxury florist concierge.
Only recommend bouquets from this catalog. When recommending a product, include [PRODUCT_ID:123] in your reply.
Keep responses concise (2-4 sentences), poetic but practical.
Catalog:
{$catalogContext}
PROMPT;

        $apiKey = config('florist.openai_api_key');

        if ($apiKey) {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout(30)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => config('florist.openai_model', 'gpt-4o-mini'),
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $message],
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => 400,
                    ]);

                if ($response->successful()) {
                    $reply = $response->json('choices.0.message.content', '');
                    return $this->parseReply($reply, $products);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this->fallbackReply($message, $products);
    }

    private function parseReply(string $reply, $products): array
    {
        $productId = null;
        $product = null;

        if (preg_match('/\[PRODUCT_ID:(\d+)\]/', $reply, $matches)) {
            $productId = (int) $matches[1];
            $product = $products->firstWhere('id', $productId);
            $reply = trim(preg_replace('/\[PRODUCT_ID:\d+\]/', '', $reply));
        }

        if (!$product) {
            $product = $this->matchProductFromText($reply, $products);
            $productId = $product?->id;
        }

        return [
            'reply' => $reply,
            'product_id' => $productId,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (float) $product->price,
            ] : null,
        ];
    }

    private function matchProductFromText(string $text, $products): ?Product
    {
        foreach ($products as $product) {
            if (Str::contains(strtolower($text), strtolower($product->name))) {
                return $product;
            }
        }

        return $products->first();
    }

    private function fallbackReply(string $message, $products): array
    {
        $lower = strtolower($message);
        $product = $products->first();

        if (Str::contains($lower, ['red', 'crimson', 'romantic', 'love', 'anniversary'])) {
            $product = $products->firstWhere('slug', 'royal-crimson-dark-red-roses') ?? $product;
        } elseif (Str::contains($lower, ['purple', 'lavender'])) {
            $product = $products->firstWhere('slug', 'lavender-symphony-purple-roses') ?? $product;
        } elseif (Str::contains($lower, ['pastel', 'soft', 'birthday', 'spring'])) {
            $product = $products->firstWhere('slug', 'aurora-bouquet-mixed-pastels') ?? $product;
        }

        $reply = sprintf(
            "What a beautiful choice to celebrate with flowers. For your request, I recommend our signature %s — crafted with premium stems and wrapped in PURE ROSE luxury presentation. [PRODUCT_ID:%d]",
            $product->name,
            $product->id
        );

        return $this->parseReply($reply, $products);
    }
}
