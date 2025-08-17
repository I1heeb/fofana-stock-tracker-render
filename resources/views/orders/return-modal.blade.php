<!-- Add visual confirmation for return process -->
<div class="modal" id="returnModal">
    <div class="modal-content">
        <h3>Return Order #{{ $order->order_number }}</h3>
        <div class="return-items">
            @foreach($order->orderItems as $item)
                <div class="item">
                    <span>{{ $item->product->name }}</span>
                    <span>Qty: {{ $item->quantity }}</span>
                    <span>Will be restored to stock</span>
                </div>
            @endforeach
        </div>
        <button onclick="processReturn()">Confirm Return</button>
    </div>
</div>