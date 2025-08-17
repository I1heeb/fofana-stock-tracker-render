<?php

return [
    // Page titles
    'title' => 'Commandes',
    'create_title' => 'Créer une nouvelle commande',
    'edit_title' => 'Modifier la commande #:id',
    'show_title' => 'Détails de la commande #:id',

    // Buttons
    'create_button' => 'Créer une commande',
    'create_new_order' => 'Créer une nouvelle commande',
    'update_order' => 'Mettre à jour la commande',
    'cancel_order' => 'Annuler la commande',
    'return_order' => 'Retourner la commande',
    'save_changes' => 'Enregistrer les modifications',

    // Form labels
    'customer' => 'Client',
    'select_customer' => 'Sélectionner un client',
    'items' => 'Articles',
    'quantity' => 'Quantité',
    'price' => 'Prix',
    'total' => 'Total',
    'status' => 'Statut',
    'notes' => 'Notes',

    // Status values
    'status_in_progress' => 'En cours',
    'status_packed' => 'Emballé',
    'status_out' => 'En livraison',
    'status_delivered' => 'Livré',
    'status_canceled' => 'Annulé',
    'status_returned' => 'Retourné',

    // Messages
    'select_to_continue' => 'Sélectionnez des articles pour continuer',
    'total_items' => 'Articles totaux',
    'order_summary' => 'Vous avez sélectionné :count articles pour :total €',
    'insufficient_stock' => 'Stock insuffisant pour :product. Disponible : :available',
    'order_created' => 'Commande créée avec succès',
    'order_updated' => 'Commande mise à jour avec succès',
    'order_canceled' => 'Commande annulée avec succès',
    'order_returned' => 'Commande retournée avec succès',

    // Table headers
    'order_id' => 'N° Commande',
    'customer_name' => 'Client',
    'items_count' => 'Articles',
    'order_date' => 'Date',
    'actions' => 'Actions',

    // Actions
    'view' => 'Voir',
    'edit' => 'Modifier',
    'cancel' => 'Annuler',
    'return' => 'Retourner',
    'delete' => 'Supprimer',

    // Filters
    'filter_orders' => 'Filtrer les commandes',
    'search_placeholder' => 'N° commande, client, produit...',
    'all_statuses' => 'Tous les statuts',
    'from_date' => 'Date de début',
    'to_date' => 'Date de fin',
    'filter' => 'Filtrer',
    'clear' => 'Effacer',

    // Validation
    'validation' => [
        'customer_required' => 'Veuillez sélectionner un client',
        'items_required' => 'Veuillez sélectionner au moins un article',
        'quantity_min' => 'La quantité doit être d\'au moins 1',
        'quantity_max' => 'La quantité ne peut pas dépasser le stock disponible',
    ],

    // Confirmations
    'confirm_cancel' => 'Êtes-vous sûr de vouloir annuler cette commande ?',
    'confirm_return' => 'Êtes-vous sûr de vouloir retourner cette commande ?',
    'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cette commande ?',

    // Empty states
    'no_orders' => 'Aucune commande trouvée correspondant à vos critères.',
    'no_items' => 'Aucun article dans cette commande.',

    // Pagination
    'showing_results' => 'Affichage de :from à :to sur :total résultats',
    'previous' => 'Précédent',
    'next' => 'Suivant',
];