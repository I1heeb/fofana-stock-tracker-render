<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class StockHistoryController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'type' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:10|max:100'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $query = StockHistory::query()
            ->with(['product', 'user', 'order'])
            ->when($request->filled('product_id'), function ($q) use ($request) {
                return $q->where('product_id', $request->product_id);
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                return $q->where('user_id', $request->user_id);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->when($request->filled(['from', 'to']), function ($q) use ($request) {
                return $q->whereBetween('created_at', [
                    $request->from . ' 00:00:00',
                    $request->to . ' 23:59:59'
                ]);
            });

        $history = $query->latest()->paginate($request->input('per_page', 20));

        return view('reports.stock-history', [
            'history' => $history,
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'types' => StockHistory::TYPES
        ]);
    }

    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'type' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $query = StockHistory::query()
            ->with(['product', 'user', 'order'])
            ->when($request->filled('product_id'), function ($q) use ($request) {
                return $q->where('product_id', $request->product_id);
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                return $q->where('user_id', $request->user_id);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->when($request->filled(['from', 'to']), function ($q) use ($request) {
                return $q->whereBetween('created_at', [
                    $request->from . ' 00:00:00',
                    $request->to . ' 23:59:59'
                ]);
            });

        $history = $query->latest()->get();

        $csvFileName = 'stock_history_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];

        $handle = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($handle, [
            'Date',
            'Product',
            'SKU',
            'Change',
            'Balance',
            'Type',
            'User',
            'Order ID',
            'Notes'
        ]);

        // Add data rows
        foreach ($history as $record) {
            fputcsv($handle, [
                $record->created_at->format('Y-m-d H:i:s'),
                $record->product->name,
                $record->product->sku,
                $record->change_text,
                $record->balance,
                $record->type_text,
                $record->user->name,
                $record->order_id,
                $record->notes
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return Response::make($content, 200, $headers);
    }
} 