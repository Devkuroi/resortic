<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('users')->onDelete('cascade');
            $table->string('number', 20);          // Número/nombre de la habitación
            $table->string('type', 50);            // sencilla, doble, suite, etc.
            $table->text('description')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->unsignedTinyInteger('capacity'); // personas
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->unique(['hotel_id', 'number']); // número único por hotel
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
