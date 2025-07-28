<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address')->nullable(); // Adresse IP de connexion
            $table->string('user_agent')->nullable(); // Navigateur/appareil
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('location')->nullable(); // Localisation (optionnel)
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('failure_reason')->nullable(); // Raison de l'échec si applicable
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable(); // Quand l'utilisateur se déconnecte
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
        Schema::dropIfExists('login_histories');
    }
}
