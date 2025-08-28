<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Order::with(['user', 'orderItems.product']);

        // Apply same filters as the index page
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('order_number', 'like', "%{$search}%");
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer',
            'Customer Email',
            'Status',
            'Total Amount',
            'Items Count',
            'Products',
            'Order Date',
            'Updated Date'
        ];
    }

    public function map($order): array
    {
        $products = $order->orderItems->map(function($item) {
            return $item->product->name . ' (Qty: ' . $item->quantity . ')';
        })->implode(', ');

        return [
            $order->order_number,
            $order->user->name ?? 'N/A',
            $order->user->email ?? 'N/A',
            ucfirst($order->status),
            $order->total_amount,
            $order->orderItems->count(),
            $products,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->updated_at->format('Y-m-d H:i:s')
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Order Number
            'B' => 20, // Customer
            'C' => 25, // Customer Email
            'D' => 15, // Status
            'E' => 15, // Total Amount
            'F' => 12, // Items Count
            'G' => 40, // Products
            'H' => 18, // Order Date
            'I' => 18, // Updated Date
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header row
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        
        // Freeze header row
        $sheet->freezePane('A2');
        
        // Add autofilter
        $sheet->setAutoFilter('A1:I1');
        
        return [];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Total Amount
            'F' => NumberFormat::FORMAT_NUMBER, // Items Count
            'H' => NumberFormat::FORMAT_DATE_DATETIME, // Order Date
            'I' => NumberFormat::FORMAT_DATE_DATETIME, // Updated Date
        ];
    }
}
