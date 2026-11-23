<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->foreignId('admin_user_id')->nullable()->constrained('users');
            $table->string('first_name');
            $table->string('second_name');
            $table->dateTime('ceremony_at');
            $table->string('ceremony_venue');
            $table->string('ceremony_city');
            $table->string('ceremony_address')->nullable();
            $table->string('ceremony_map_url', 2048)->nullable();
            $table->dateTime('reception_at');
            $table->string('reception_venue');
            $table->string('reception_city');
            $table->string('reception_address')->nullable();
            $table->string('reception_map_url', 2048)->nullable();
            $table->dateTime('songs_close_at');
            $table->dateTime('games_close_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_settings');
    }
};
