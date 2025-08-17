<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class CleanTestProducts extends Command
{
    protected $signature = 'products:clean-test';
    protected $description = 'Remove test products with Lorem Ipsum names';

    public function handle()
    {
        $this->info('Searching for test products...');
        
        // Mots-clés de test à supprimer
        $testKeywords = [
            'nour', 'pariatur', 'vel', 'corrupti', 'provident', 'blanditiis',
            'facere', 'voluptas', 'sed', 'placeat', 'soluta', 'fugit',
            'rem', 'et', 'veritatis', 'quasi', 'illum', 'sequi',
            'deleniti', 'ipsum', 'quod', 'in', 'ut', 'molestiae',
            'non', 'nulla', 'autem', 'officiis', 'lorem', 'dolor',
            'sit', 'amet', 'consectetur', 'adipiscing', 'elit'
        ];
        
        $query = Product::query();
        
        // Construire la requête pour trouver les produits avec ces mots-clés
        foreach ($testKeywords as $index => $keyword) {
            if ($index === 0) {
                $query->where('name', 'like', "%{$keyword}%");
            } else {
                $query->orWhere('name', 'like', "%{$keyword}%");
            }
        }
        
        $testProducts = $query->get();
        
        if ($testProducts->count() === 0) {
            $this->info('No test products found.');
            return;
        }
        
        $this->info("Found {$testProducts->count()} test products:");
        
        foreach ($testProducts as $product) {
            $this->line("- ID: {$product->id}, Name: {$product->name}, SKU: {$product->sku}");
        }
        
        if ($this->confirm('Do you want to delete these test products?')) {
            $deletedCount = 0;
            
            foreach ($testProducts as $product) {
                $product->delete();
                $deletedCount++;
                $this->line("Deleted: {$product->name}");
            }
            
            $this->success("Successfully deleted {$deletedCount} test products!");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
