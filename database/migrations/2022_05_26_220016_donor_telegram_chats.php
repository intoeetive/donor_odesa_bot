<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('donor_telegram_chats', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');

            $table->foreignId('telegraph_bot_id')->constrained('telegraph_bots')->cascadeOnDelete();

            $table->foreignId('donor_id')->constrained('donors')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['chat_id', 'telegraph_bot_id']);
            $table->index(['donor_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donor_telegram_chats');
    }
};
