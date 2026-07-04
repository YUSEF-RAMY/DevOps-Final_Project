<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FloristAiService;
use Illuminate\Http\Request;

class AIClientController extends Controller
{
    public function __construct(private FloristAiService $floristAi)
    {
    }

    public function consult(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $result = $this->floristAi->consult(
            $data['message'],
            $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'reply' => $result['reply'],
            'product' => $result['product'],
            'product_id' => $result['product_id'],
        ]);
    }
}
