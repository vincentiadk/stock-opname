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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->index();
            $table->integer('stockopname_id')->nullable()->index();
            $table->string('stockopname_name')->nullable();
            $table->integer('location_id')->nullable()->index();
            $table->string('location_name')->nullable();
            $table->integer('location_shelf_id')->nullable()->index();
            $table->string('location_shelf_name')->nullable();
            $table->integer('location_rugs_id')->nullable()->index();
            $table->string('location_rugs_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
