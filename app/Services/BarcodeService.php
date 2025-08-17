<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;

class BarcodeService
{
    public function generateBarcode(Product $product): string
    {
        if ($product->barcode) {
            return $product->barcode;
        }

        // Generate EAN-13 barcode
        $barcode = $this->generateEAN13($product->id);
        $product->update(['barcode' => $barcode]);
        
        return $barcode;
    }

    public function findProductByBarcode(string $barcode): ?Product
    {
        return Product::where('barcode', $barcode)->first();
    }

    public function validateBarcode(string $barcode): bool
    {
        // Basic EAN-13 validation
        if (strlen($barcode) !== 13 || !ctype_digit($barcode)) {
            return false;
        }

        // Check digit validation
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$barcode[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit === (int)$barcode[12];
    }

    private function generateEAN13(int $productId): string
    {
        // Simple EAN-13 generation (company prefix + product code + check digit)
        $companyPrefix = '123456'; // Your company prefix
        $productCode = str_pad($productId, 5, '0', STR_PAD_LEFT);
        $partial = $companyPrefix . $productCode;
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$partial[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $partial . $checkDigit;
    }
}