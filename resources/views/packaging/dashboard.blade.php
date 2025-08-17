<div class="packaging-dashboard">
    <div class="grid grid-cols-4 gap-6">
        <div class="stat-card">
            <h3>Orders Ready to Pack</h3>
            <span class="text-3xl">{{ $stats['ready_to_pack'] }}</span>
        </div>
        <div class="stat-card">
            <h3>Currently Packing</h3>
            <span class="text-3xl">{{ $stats['in_progress'] }}</span>
        </div>
        <div class="stat-card">
            <h3>Packed Today</h3>
            <span class="text-3xl">{{ $stats['packed_today'] }}</span>
        </div>
        <div class="stat-card">
            <h3>Shipped Today</h3>
            <span class="text-3xl">{{ $stats['shipped_today'] }}</span>
        </div>
    </div>
</div>