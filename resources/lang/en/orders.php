<?php

return [
    // Page titles
    'title' => 'Orders',
    'create_title' => 'Create New Order',
    'edit_title' => 'Edit Order #:id',
    'show_title' => 'Order Details #:id',

    // Buttons
    'create_button' => 'Create Order',
    'create_new_order' => 'Create New Order',
    'update_order' => 'Update Order',
    'cancel_order' => 'Cancel Order',
    'return_order' => 'Return Order',
    'save_changes' => 'Save Changes',
    'create_new_product' => 'Create New product',

    // Form labels
    'customer' => 'Customer',
    'select_customer' => 'Select Customer',
    'items' => 'Items',
    'quantity' => 'Quantity',
    'price' => 'Price',
    'total' => 'Total',
    'status' => 'Status',
    'notes' => 'Notes',

    // Status values
    'status_in_progress' => 'In Progress',
    'status_packed' => 'Packed',
    'status_out' => 'Out for Delivery',
    'status_delivered' => 'Delivered',
    'status_canceled' => 'Canceled',
    'status_returned' => 'Returned',

    // Messages
    'select_to_continue' => 'Select items to continue',
    'total_items' => 'Total items',
    'order_summary' => 'You have selected :count items for $:total',
    'insufficient_stock' => 'Insufficient stock for :product. Available: :available',
    'order_created' => 'Order created successfully',
    'order_updated' => 'Order updated successfully',
    'order_canceled' => 'Order canceled successfully',
    'order_returned' => 'Order returned successfully',

    // Table headers
    'order_id' => 'Order ID',
    'customer_name' => 'Customer',
    'items_count' => 'Items',
    'order_date' => 'Date',
    'actions' => 'Actions',

    // Actions
    'view' => 'View',
    'edit' => 'Edit',
    'cancel' => 'Cancel',
    'return' => 'Return',
    'delete' => 'Delete',

    // Filters
    'filter_orders' => 'Filter Orders',
    'search_placeholder' => 'Order ID, customer, product...',
    'all_statuses' => 'All Statuses',
    'from_date' => 'From Date',
    'to_date' => 'To Date',
    'filter' => 'Filter',
    'clear' => 'Clear',

    // Validation
    'validation' => [
        'customer_required' => 'Please select a customer',
        'items_required' => 'Please select at least one item',
        'quantity_min' => 'Quantity must be at least 1',
        'quantity_max' => 'Quantity cannot exceed available stock',
    ],

    // Confirmations
    'confirm_cancel' => 'Are you sure you want to cancel this order?',
    'confirm_return' => 'Are you sure you want to return this order?',
    'confirm_delete' => 'Are you sure you want to delete this order?',

    // Empty states
    'no_orders' => 'No orders found matching your criteria.',
    'no_items' => 'No items in this order.',

    // Pagination
    'showing_results' => 'Showing :from to :to of :total results',
    'previous' => 'Previous',
    'next' => 'Next',
];