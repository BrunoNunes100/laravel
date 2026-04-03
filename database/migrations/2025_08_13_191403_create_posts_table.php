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
            $table->foreignId('user_id')  // adiciona a referência ao usuário
                  ->constrained()        // cria a foreign key automaticamente apontando para 'users.id'
                  ->onDelete('cascade'); // se o usuário for deletado, deleta os posts dele
            $table->string('description', 255);
            $table->string('picture', 255)->nullable(); // foto pode ser nula
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
