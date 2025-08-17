<<<<<<< HEAD
# 📦 Fofana Stock Tracker

> **Système de gestion de stock professionnel développé par Nour Amara**

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://postgresql.org)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

## 🎯 À Propos

**Fofana Stock Tracker** est une application web moderne de gestion de stock développée avec Laravel 12. Elle offre une interface intuitive pour gérer les produits, commandes et inventaires avec des fonctionnalités avancées.

### ✨ Fonctionnalités Principales

- 📦 **Gestion des Produits** - CRUD complet avec recherche avancée et filtres
- 🛒 **Gestion des Commandes** - Suivi complet du cycle de vie des commandes
- 📊 **Tableau de Bord** - Analytics en temps réel et rapports détaillés
- 👥 **Multi-utilisateurs** - Système de rôles et permissions
- 📱 **PWA** - Application web progressive avec support hors ligne
- ♿ **Accessibilité** - Conforme aux standards WCAG
- 🔍 **Recherche Avancée** - Filtres multiples avec interface collapse

## 🛠️ Stack Technique

### Backend
- **Laravel 12.0** - Framework PHP moderne
- **PHP 8.3** - Dernière version avec fonctionnalités avancées
- **PostgreSQL 15** - Base de données relationnelle robuste
- **Redis** - Cache et sessions

### Frontend
- **Blade Templates** - Moteur de templates Laravel
- **TailwindCSS 4.0** - Framework CSS utility-first
- **Bootstrap 5.3** - Composants UI
- **Alpine.js** - Framework JavaScript léger
- **Chart.js** - Visualisation de données

### Qualité & Tests
- **PHPUnit** - Tests unitaires et fonctionnels
- **Laravel Dusk** - Tests E2E
- **PHPStan Level 8** - Analyse statique
- **PHP CS Fixer** - Standards de code PSR-12
- **Lighthouse CI** - Audits de performance
- **Pa11y** - Tests d'accessibilité

## 🚀 Installation

### Prérequis
- PHP 8.3+
- Composer
- Node.js 20+
- PostgreSQL 15+
- Redis (optionnel)

### Étapes d'installation

```bash
# 1. Cloner le repository
git clone https://github.com/nouramara123/fofana-stock-tracker.git
cd fofana-stock-tracker

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances Node.js
npm install

# 4. Configuration de l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fofana_stock
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 6. Migrations et données de test
php artisan migrate --seed

# 7. Compiler les assets
npm run build

# 8. Lancer le serveur de développement
php artisan serve
```

## 📊 Qualité du Code

- ✅ **PHPStan Level 8** - Analyse statique au plus haut niveau
- ✅ **PSR-12 Compliant** - Standards de code PHP
- ✅ **Test Coverage** - Tests unitaires, fonctionnels et E2E
- ✅ **Accessibility** - Conforme WCAG 2.1 AA
- ✅ **Performance** - Optimisé avec Lighthouse CI

## 🔧 Scripts Disponibles

```bash
# Développement
npm run dev          # Serveur de développement Vite
npm run build        # Build de production
npm run watch        # Watch mode pour développement

# Tests
php artisan test     # Tests PHPUnit
php artisan dusk     # Tests Dusk E2E
vendor/bin/phpstan   # Analyse statique

# Code Quality
vendor/bin/php-cs-fixer fix  # Formatage du code
composer audit               # Audit de sécurité
```

## 📸 Screenshots

### Dashboard Principal
![Dashboard](docs/screenshots/dashboard.png)

### Gestion des Produits
![Products](docs/screenshots/products.png)

### Interface Mobile
![Mobile](docs/screenshots/mobile.png)

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 License

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**Nour Amara** - [@nouramara123](https://github.com/nouramara123)

- 📧 Email: nour@gmail.com
- 💼 LinkedIn: [Votre LinkedIn]
- 🐦 Twitter: [Votre Twitter]

## 🙏 Remerciements

- Laravel Team pour le framework exceptionnel
- Communauté open source pour les packages utilisés
- Tous les contributeurs du projet

---

⭐ **N'hésitez pas à donner une étoile si ce projet vous a aidé !**



=======
# fofana-stock-tracker
Système de gestion de stock moderne avec Laravel 12
>>>>>>> eb552ccfc3641ec8bf6b2c2f8390f3403b85e28e
