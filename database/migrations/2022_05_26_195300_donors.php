<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('telegraph_bot_id')->constrained('telegraph_bots')->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->enum('blood_type', ['', 'I (1)', 'II (2)', 'III (3)', 'IV (4)']);
            $table->enum('blood_rh', ['', '+', '-']);

            $table->date('last_request_date')->nullable();
            $table->date('last_donor_date')->nullable();

            $table->int('sheet_row')->nullable();

            $table->timestamps();

            $table->index(['blood_type', 'blood_rh']);
        });
    }
};
