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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string("reference")->unique();
            $table->unsignedBigInteger("app_id");
            $table->decimal("amount", 15, 2);
            $table->string("currency");
            $table->string("email");
            $table->string("status")->default("pending");
            $table->string("gateway_response")->nullable();
            $table->timestamp("paid_at")->nullable();
            $table->string("channel")->nullable();
            $table->text("raw_payload")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
