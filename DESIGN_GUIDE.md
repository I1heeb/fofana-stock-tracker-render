# Guide du Nouveau Design - Fofana Stock

## 🎨 Vue d'ensemble

Le nouveau design de Fofana Stock présente une interface moderne et professionnelle avec une sidebar rétractable et un thème élégant en bleu navy et jaune moutarde.

## 🌈 Palette de Couleurs

### Couleurs Principales

- **Bleu Navy (Navy-900)** : `#1e293b` - Couleur principale pour header, sidebar, boutons
- **Jaune Moutarde (Mustard-500)** : `#d4a017` - Couleur d'accent pour boutons et éléments importants
- **Blanc** : `#ffffff` - Fond principal de l'application
- **Gris Clair** : `#f1f5f9` - Sections secondaires et formulaires

### Utilisation des Couleurs

```css
/* Couleurs Navy */
.bg-navy-900    /* Fond principal */
.bg-navy-800    /* Fond secondaire */
.bg-navy-700    /* Hover states */

/* Couleurs Mustard */
.bg-mustard-500 /* Accent principal */
.bg-mustard-400 /* Accent clair */
.bg-mustard-600 /* Accent foncé */
```

## 📱 Sidebar Rétractable

### Fonctionnalités

- **Desktop** : Sidebar fixe à gauche (256px de largeur)
- **Mobile/Tablette** : Sidebar en overlay avec bouton hamburger
- **Animation** : Transitions fluides (300ms ease-in-out)
- **Navigation** : Icônes SVG + texte pour chaque section

### Navigation Disponible

1. **Dashboard** - Tableau de bord principal
2. **Orders** - Gestion des commandes
3. **Products** - Gestion des produits
4. **Reports** - Rapports et analyses
5. **User Management** - Gestion des utilisateurs (admin uniquement)

## 🔤 Typographie

### Police Principale : Poppins

- **Poids disponibles** : 300, 400, 500, 600, 700
- **Caractéristiques** : Sans-serif, moderne, lisible
- **Chargement** : Google Fonts avec preconnect pour les performances

### Classes de Typographie

```css
.heading-1      /* Titres principaux (3xl, font-bold) */
.heading-2      /* Titres secondaires (2xl, font-semibold) */
.heading-3      /* Titres tertiaires (xl, font-semibold) */
.text-body      /* Texte de paragraphe (leading-relaxed) */
.text-muted     /* Texte secondaire (text-gray-500, text-sm) */
```

## 🔘 Boutons et Interactions

### Types de Boutons

```css
.btn-primary    /* Bleu navy, texte blanc */
.btn-secondary  /* Jaune moutarde, texte navy */
.btn-outline    /* Bordure navy, fond transparent */
```

### Effets Hover

- **Scale** : `hover:scale-105` (zoom léger)
- **Shadow** : `hover:shadow-lg` (ombre portée)
- **Transition** : `transition-all duration-200` (animation fluide)

## 📐 Responsive Design

### Breakpoints

- **Mobile** : `< 768px`
- **Tablette** : `768px - 1024px`
- **Desktop** : `> 1024px`

### Comportements Responsifs

#### Mobile (< 768px)
- Sidebar en overlay
- Bouton hamburger visible
- Texte responsive plus petit
- Touch targets optimisés (44px minimum)

#### Tablette (768px - 1024px)
- Sidebar peut être en overlay ou fixe
- Grilles adaptées (2 colonnes max)
- Espacement ajusté

#### Desktop (> 1024px)
- Sidebar fixe
- Effets hover activés
- Grilles complètes
- Espacement généreux

## 🎯 Accessibilité

### Fonctionnalités Implémentées

- **Navigation clavier** : Tab, Escape pour fermer la sidebar
- **ARIA labels** : Boutons et éléments interactifs
- **Contraste** : Respect des ratios WCAG AA
- **Focus visible** : Anneaux de focus personnalisés
- **Touch targets** : Minimum 44px sur mobile

### Classes d'Accessibilité

```css
.focus-visible-ring  /* Anneau de focus personnalisé */
.btn-touch          /* Taille minimum pour touch */
.sr-only            /* Screen reader only */
```

## 🔧 Composants Personnalisés

### Cards

```css
.card           /* Carte de base avec ombre */
.card-header    /* En-tête de carte avec bordure */
```

### Formulaires

```css
.form-input     /* Input stylisé avec focus mustard */
.form-label     /* Label en navy bold */
```

### Navigation

```css
.nav-link           /* Lien de navigation header */
.sidebar-link       /* Lien de sidebar */
.sidebar-link-active /* Lien actif (mustard) */
```

## 🚀 Utilisation

### Page de Test

Visitez `/test-design` pour voir tous les éléments du design en action.

### Intégration dans vos Pages

```blade
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-header">
            <h2 class="heading-2">Titre de Section</h2>
        </div>
        
        <p class="text-body">Contenu de votre page...</p>
        
        <div class="flex gap-4">
            <button class="btn-primary">Action Principale</button>
            <button class="btn-outline">Action Secondaire</button>
        </div>
    </div>
</div>
@endsection
```

## 📊 Performance

- **CSS optimisé** : Tailwind CSS avec purge
- **Fonts optimisées** : Preconnect Google Fonts
- **Animations** : GPU-accelerated transforms
- **Bundle size** : ~75KB CSS, ~97KB JS (gzippé)

## 🔄 Maintenance

### Ajout de Nouvelles Couleurs

Modifiez `tailwind.config.js` dans la section `colors` :

```javascript
colors: {
  'custom': {
    500: '#your-color',
  }
}
```

### Nouveaux Composants

Ajoutez dans `resources/css/app.css` sous `@layer components` :

```css
.your-component {
  @apply bg-navy-900 text-white p-4 rounded-lg;
}
```

---

**Développé avec ❤️ pour Fofana Stock**
