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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable(); // e.g., site, manual, import
            $table->string('status')->default('new'); // new, contacting, qualified, lost
            $table->foreignId('pipeline_stage_id')->nullable()->constrained('pipeline_stages')->onDelete('set null');
            $table->unsignedBigInteger('customer_id')->nullable(); // Link if converted to customer
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
