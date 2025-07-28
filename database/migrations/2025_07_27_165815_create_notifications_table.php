<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Client qui reçoit la notification
            $table->string('fcm_token')->nullable(); // Token FCM du client
            $table->string('title'); // Titre de la notification
            $table->text('message'); // Message de la notification
            $table->string('type'); // Type: 'livraison_status', 'commande_created', 'livraison_assigned', etc.
            $table->json('data')->nullable(); // Données supplémentaires (ID commande, ID livraison, etc.)
            $table->boolean('is_read')->default(false); // Si la notification a été lue
            $table->timestamp('read_at')->nullable(); // Quand la notification a été lue
            $table->boolean('is_sent')->default(false); // Si la notification a été envoyée via FCM
            $table->timestamp('sent_at')->nullable(); // Quand la notification a été envoyée
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
        Schema::dropIfExists('notifications');
    }
}
