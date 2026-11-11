<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->unsignedTinyInteger('guests')->nullable();
            $table->string('cottage_preference')->nullable();
            $table->text('message')->nullable();
            $table->string('source')->default('other');
            $table->string('status')->default('new')->index();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
