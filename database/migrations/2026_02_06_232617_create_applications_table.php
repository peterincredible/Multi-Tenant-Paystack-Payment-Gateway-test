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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->default('Empty');
            $table->string('paystack_public_key');
            $table->string('paystack_private_key');
            $table->string('callback_url');
            $table->string('webhook_url');
            $table->timestamps();
        });
    }
    //php artisan make:controller Api/ApplicationController

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
