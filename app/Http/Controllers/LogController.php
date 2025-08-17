<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        try {
            $logs =  Log::with(['user', 'order', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        
            return view('logs.index', compact('logs'));
        } catch (\Exception $e) {
            \Log::error('Logs index error: ' . $e->getMessage());
            
            // Return empty collection if there's an error
            $logs = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 
                0, 
                20, 
                1, 
                ['path' => request()->url()]
            );
            
            return view('logs.index', compact('logs'))
                ->with('error', 'Unable to load logs. Please try again.');
        }
    }

    public function show(Log $log)
    {
        $log->load(['user', 'order', 'product']);
        return view('logs.show', compact('log'));
    }
}


