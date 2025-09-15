// Arquivo: 2025_08_13_191403_create_posts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('data', 255); // REMOVA AS ASPAS DO '255'
            $table->string('description', 255); // REMOVA AS ASPAS DO '255'
            $table->string('picture', 255); // REMOVA AS ASPAS DO '255'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};