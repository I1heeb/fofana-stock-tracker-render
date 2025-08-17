// Add real-time stock monitoring
Echo.channel('stock-updates')
    .listen('StockUpdated', (e) => {
        updateStockDisplay(e.product.id, e.product.stock_quantity);
        if (e.product.is_low_stock) {
            showLowStockAlert(e.product);
        }
    });