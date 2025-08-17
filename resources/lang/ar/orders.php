<?php

return [
    // Page titles
    'title' => 'الطلبات',
    'create_title' => 'إنشاء طلب جديد',
    'edit_title' => 'تعديل الطلب #:id',
    'show_title' => 'تفاصيل الطلب #:id',

    // Buttons
    'create_button' => 'إنشاء طلب',
    'create_new_order' => 'إنشاء طلب جديد',
    'update_order' => 'تحديث الطلب',
    'cancel_order' => 'إلغاء الطلب',
    'return_order' => 'إرجاع الطلب',
    'save_changes' => 'حفظ التغييرات',

    // Form labels
    'customer' => 'العميل',
    'select_customer' => 'اختر عميل',
    'items' => 'العناصر',
    'quantity' => 'الكمية',
    'price' => 'السعر',
    'total' => 'المجموع',
    'status' => 'الحالة',
    'notes' => 'ملاحظات',

    // Status values
    'status_in_progress' => 'قيد التنفيذ',
    'status_packed' => 'معبأ',
    'status_out' => 'في الطريق',
    'status_delivered' => 'تم التسليم',
    'status_canceled' => 'ملغي',
    'status_returned' => 'مرتجع',

    // Messages
    'select_to_continue' => 'اختر العناصر للمتابعة',
    'total_items' => 'إجمالي العناصر',
    'order_summary' => 'لقد اخترت :count عناصر بمبلغ :total ريال',
    'insufficient_stock' => 'مخزون غير كافي لـ :product. المتاح: :available',
    'order_created' => 'تم إنشاء الطلب بنجاح',
    'order_updated' => 'تم تحديث الطلب بنجاح',
    'order_canceled' => 'تم إلغاء الطلب بنجاح',
    'order_returned' => 'تم إرجاع الطلب بنجاح',

    // Table headers
    'order_id' => 'رقم الطلب',
    'customer_name' => 'العميل',
    'items_count' => 'العناصر',
    'order_date' => 'التاريخ',
    'actions' => 'الإجراءات',

    // Actions
    'view' => 'عرض',
    'edit' => 'تعديل',
    'cancel' => 'إلغاء',
    'return' => 'إرجاع',
    'delete' => 'حذف',

    // Filters
    'filter_orders' => 'تصفية الطلبات',
    'search_placeholder' => 'رقم الطلب، العميل، المنتج...',
    'all_statuses' => 'جميع الحالات',
    'from_date' => 'من تاريخ',
    'to_date' => 'إلى تاريخ',
    'filter' => 'تصفية',
    'clear' => 'مسح',

    // Validation
    'validation' => [
        'customer_required' => 'يرجى اختيار عميل',
        'items_required' => 'يرجى اختيار عنصر واحد على الأقل',
        'quantity_min' => 'يجب أن تكون الكمية 1 على الأقل',
        'quantity_max' => 'لا يمكن أن تتجاوز الكمية المخزون المتاح',
    ],

    // Confirmations
    'confirm_cancel' => 'هل أنت متأكد من إلغاء هذا الطلب؟',
    'confirm_return' => 'هل أنت متأكد من إرجاع هذا الطلب؟',
    'confirm_delete' => 'هل أنت متأكد من حذف هذا الطلب؟',

    // Empty states
    'no_orders' => 'لم يتم العثور على طلبات تطابق معاييرك.',
    'no_items' => 'لا توجد عناصر في هذا الطلب.',

    // Pagination
    'showing_results' => 'عرض :from إلى :to من :total نتيجة',
    'previous' => 'السابق',
    'next' => 'التالي',
];