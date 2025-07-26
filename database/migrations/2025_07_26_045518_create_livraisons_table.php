<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivraisonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Le livreur
            $table->enum('status', ['en_attente', 'assignee', 'en_cours', 'livree', 'annulee'])->default('en_attente');
            $table->timestamp('assigned_at')->nullable(); // Quand la livraison a été assignée
            $table->timestamp('started_at')->nullable(); // Quand le livreur a commencé
            $table->timestamp('delivered_at')->nullable(); // Quand la livraison a été terminée
            $table->text('notes')->nullable(); // Notes du livreur
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('livraisons');
    }
}