<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BarcodeController extends Controller
{
    public function __construct(
        private BarcodeService $barcodeService
    ) {}

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = $request->input('barcode');

        if (!$this->barcodeService->validateBarcode($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid barcode format',
            ], 400);
        }

        $product = $this->barcodeService->findProductByBarcode($barcode);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'barcode' => $product->barcode,
            ],
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);
        $barcode = $this->barcodeService->generateBarcode($product);

        return response()->json([
            'success' => true,
            'barcode' => $barcode,
        ]);
    }
}