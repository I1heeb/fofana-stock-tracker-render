<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Product::with('supplier');

        // Apply same filters as the index page
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['stock_status'])) {
            switch ($this->filters['stock_status']) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0)
                          ->whereRaw('stock_quantity > COALESCE(low_stock_threshold, 10)');
                    break;
                case 'low_stock':
                    $query->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 10)')
                          ->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '=', 0);
                    break;
            }
        }

        if (!empty($this->filters['price_min'])) {
            $query->where('price', '>=', $this->filters['price_min']);
        }

        if (!empty($this->filters['price_max'])) {
            $query->where('price', '<=', $this->filters['price_max']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Barcode',
            'Description',
            'Price',
            'Stock Quantity',
            'Low Stock Threshold',
            'Minimum Stock',
            'Stock Status',
            'Supplier',
            'Created Date',
            'Updated Date'
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->sku,
            $product->barcode ?? '',
            $product->description ?? '',
            $product->price,
            $product->stock_quantity,
            $product->low_stock_threshold,
            $product->minimum_stock,
            $this->getStockStatus($product),
            $product->supplier->name ?? '',
            $product->created_at->format('Y-m-d H:i:s'),
            $product->updated_at->format('Y-m-d H:i:s')
        ];
    }

    private function getStockStatus($product): string
    {
        if ($product->stock_quantity <= 0) {
            return 'Out of Stock';
        } elseif ($product->stock_quantity <= $product->minimum_stock) {
            return 'Critical';
        } elseif ($product->stock_quantity <= $product->low_stock_threshold) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Product Name
            'B' => 15, // SKU
            'C' => 15, // Barcode
            'D' => 30, // Description
            'E' => 12, // Price
            'F' => 15, // Stock Quantity
            'G' => 18, // Low Stock Threshold
            'H' => 15, // Minimum Stock
            'I' => 15, // Stock Status
            'J' => 20, // Supplier
            'K' => 18, // Created Date
            'L' => 18, // Updated Date
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        
        // Freeze header row
        $sheet->freezePane('A2');
        
        // Add autofilter
        $sheet->setAutoFilter('A1:L1');
        
        return [];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Price
            'F' => NumberFormat::FORMAT_NUMBER, // Stock Quantity
            'G' => NumberFormat::FORMAT_NUMBER, // Low Stock Threshold
            'H' => NumberFormat::FORMAT_NUMBER, // Minimum Stock
            'K' => NumberFormat::FORMAT_DATE_DATETIME, // Created Date
            'L' => NumberFormat::FORMAT_DATE_DATETIME, // Updated Date
        ];
    }
}
