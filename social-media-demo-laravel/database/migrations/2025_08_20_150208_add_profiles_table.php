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
        Schema::create("profiles", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->unique();
            $table->string("display_name")->nullable(); // If not set default to the username
            $table->string("bio")->nullable(); // Can be 
            $table->string("avatar_url")->nullable();
            $table->string("banner_url")->nullable();
            $table->string("timezone")->nullable();
            $table->string("locale")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("profiles");
    }
};
