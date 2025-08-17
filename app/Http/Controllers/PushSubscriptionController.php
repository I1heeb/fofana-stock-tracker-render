<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string'
        ]);

        $user = auth()->user();
        
        $user->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('keys.p256dh'),
            $request->input('keys.auth')
        );

        return response()->json([
            'success' => true,
            'message' => __('Push notifications enabled successfully')
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $user->deletePushSubscription($request->input('endpoint'));

        return response()->json([
            'success' => true,
            'message' => __('Push notifications disabled')
        ]);
    }

    public function test(): JsonResponse
    {
        $user = auth()->user();
        
        $user->notify(new \App\Notifications\TestNotification());

        return response()->json([
            'success' => true,
            'message' => __('Test notification sent')
        ]);
    }
}