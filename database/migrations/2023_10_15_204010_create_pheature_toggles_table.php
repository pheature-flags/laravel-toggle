<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pheature_toggles', function (Blueprint $table) {
            $table->string('feature_id');
            $table->primary('feature_id');
            $table->string('name');
            $table->boolean('enabled');
            $table->json('strategies');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pheature_toggles');
    }
};
