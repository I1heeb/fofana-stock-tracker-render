<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cette migration est un doublon - ne rien faire
        // Les index sont déjà créés par 2024_01_15_add_performance_indexes
    }

    public function down(): void
    {
        // Ne rien faire - les index sont gérés par l'autre migration
    }
};


