<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('telegraph_chats', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('name', '');

            $table->foreignId('telegraph_bot_id')->constrained('telegraph_bots')->cascadeOnDelete();
            $table->string('phone', '');
            $table->enum('blood_type', ['I (1)', 'II (2)', 'III (3)', 'IV (4)']);
            $table->enum('blood_rh', ['+', '-']);

            $table->date('last_request_date');
            $table->date('last_donor_date');

            $table->timestamps();

            $table->unique(['chat_id', 'telegraph_bot_id']);
        });
    }
};
