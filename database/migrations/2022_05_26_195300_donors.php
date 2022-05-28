<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('telegraph_bot_id')->constrained('telegraph_bots')->cascadeOnDelete(); //Chat ID in Telegram

            $table->unsignedTinyInteger('opt_in')->default('1'); //1 - active, 0 - temporary pause notifications

            $table->string('name')->nullable(); // Name
            $table->string('phone')->nullable(); // Phone number
            $table->unsignedInteger('birth_year')->nullable(); // Year of birth to calculate age
            $table->unsignedTinyInteger('weight_ok')->nullable(); // 1 - weight is over 55 kg
            $table->unsignedTinyInteger('contras')->nullable(); // 1 - has contraindications, 0 - can be accepted as donor
            $table->enum('blood_type', ['', 'I (1)', 'II (2)', 'III (3)', 'IV (4)']); // Blood type
            $table->enum('blood_rh', ['', '+', '-']); // RH

            $table->date('last_request_date')->nullable();
            $table->date('last_donor_date')->nullable();

            $table->timestamps();

            $table->index(['blood_type', 'blood_rh']);
        });
    }
};
