@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="heading-1">Test du Nouveau Design</h1>
        <p class="text-body">Cette page teste le nouveau design avec sidebar rétractable, couleurs navy et mustard, et responsivité.</p>
    </div>

    <!-- Color Palette Demo -->
    <div class="card">
        <div class="card-header">
            <h2 class="heading-2">Palette de Couleurs</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Navy Colors -->
            <div class="space-y-2">
                <h3 class="heading-3">Navy Blue</h3>
                <div class="bg-navy-900 text-white p-4 rounded-lg">Navy 900 - Principal</div>
                <div class="bg-navy-800 text-white p-4 rounded-lg">Navy 800</div>
                <div class="bg-navy-700 text-white p-4 rounded-lg">Navy 700</div>
            </div>
            
            <!-- Mustard Colors -->
            <div class="space-y-2">
                <h3 class="heading-3">Mustard Yellow</h3>
                <div class="bg-mustard-500 text-navy-900 p-4 rounded-lg font-semibold">Mustard 500 - Principal</div>
                <div class="bg-mustard-400 text-navy-900 p-4 rounded-lg">Mustard 400</div>
                <div class="bg-mustard-600 text-white p-4 rounded-lg">Mustard 600</div>
            </div>
            
            <!-- Neutral Colors -->
            <div class="space-y-2">
                <h3 class="heading-3">Neutres</h3>
                <div class="bg-white border border-gray-200 p-4 rounded-lg">Blanc - Fond</div>
                <div class="bg-gray-100 p-4 rounded-lg">Gris Clair</div>
                <div class="bg-gray-500 text-white p-4 rounded-lg">Gris Moyen</div>
            </div>
        </div>
    </div>

    <!-- Button Styles Demo -->
    <div class="card">
        <div class="card-header">
            <h2 class="heading-2">Styles de Boutons</h2>
        </div>
        
        <div class="space-y-4">
            <div class="flex flex-wrap gap-4">
                <button class="btn-primary">Bouton Principal</button>
                <button class="btn-secondary">Bouton Secondaire</button>
                <button class="btn-outline">Bouton Outline</button>
            </div>
            
            <div class="flex flex-wrap gap-4">
                <button class="btn-primary btn-touch">Touch Friendly</button>
                <button class="btn-secondary desktop-hover">Desktop Hover</button>
            </div>
        </div>
    </div>

    <!-- Typography Demo -->
    <div class="card">
        <div class="card-header">
            <h2 class="heading-2">Typographie Poppins</h2>
        </div>
        
        <div class="space-y-4">
            <h1 class="heading-1">Titre Principal (H1)</h1>
            <h2 class="heading-2">Titre Secondaire (H2)</h2>
            <h3 class="heading-3">Titre Tertiaire (H3)</h3>
            
            <p class="text-body">
                Ceci est un paragraphe avec la police Poppins. Le texte est aéré et moderne, 
                parfait pour une boutique en ligne tunisienne. La lecture est fluide et agréable.
            </p>
            
            <p class="text-muted">Texte secondaire en gris plus clair.</p>
            
            <div class="space-y-2">
                <p class="text-responsive-sm">Texte responsive small</p>
                <p class="text-responsive-base">Texte responsive base</p>
                <p class="text-responsive-lg">Texte responsive large</p>
                <p class="text-responsive-xl">Texte responsive extra large</p>
            </div>
        </div>
    </div>

    <!-- Form Elements Demo -->
    <div class="card">
        <div class="card-header">
            <h2 class="heading-2">Éléments de Formulaire</h2>
        </div>
        
        <form class="space-y-4">
            <div>
                <label class="form-label">Nom du produit</label>
                <input type="text" class="form-input" placeholder="Entrez le nom du produit">
            </div>
            
            <div>
                <label class="form-label">Description</label>
                <textarea class="form-input" rows="3" placeholder="Description du produit"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Prix</label>
                    <input type="number" class="form-input" placeholder="0.00">
                </div>
                <div>
                    <label class="form-label">Quantité</label>
                    <input type="number" class="form-input" placeholder="0">
                </div>
            </div>
            
            <div class="flex gap-4">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <button type="button" class="btn-outline">Annuler</button>
            </div>
        </form>
    </div>

    <!-- Responsive Grid Demo -->
    <div class="card">
        <div class="card-header">
            <h2 class="heading-2">Grille Responsive</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @for($i = 1; $i <= 8; $i++)
            <div class="bg-gradient-to-br from-navy-900 to-navy-700 text-white p-6 rounded-lg text-center">
                <h3 class="font-semibold mb-2">Carte {{ $i }}</h3>
                <p class="text-sm opacity-90">Contenu de la carte responsive</p>
            </div>
            @endfor
        </div>
    </div>

    <!-- Mobile Features Demo -->
    <div class="card">
        <div class="card-header">
            <h2 class="heading-2">Fonctionnalités Mobile</h2>
        </div>
        
        <div class="space-y-4">
            <p class="text-body">
                Sur mobile, la sidebar se transforme en menu hamburger. 
                Les boutons sont optimisés pour le touch (44px minimum).
                Le design s'adapte automatiquement à toutes les tailles d'écran.
            </p>
            
            <div class="bg-mustard-100 border-l-4 border-mustard-500 p-4 rounded">
                <p class="text-mustard-800 font-medium">
                    💡 Astuce: Testez la responsivité en redimensionnant votre navigateur ou en utilisant les outils de développement.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
