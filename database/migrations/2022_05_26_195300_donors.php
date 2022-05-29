<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable(); // Name
            $table->string('phone')->nullable(); // Phone number
            $table->unsignedInteger('birth_year')->nullable(); // Year of birth to calculate age
            $table->unsignedTinyInteger('weight_ok')->nullable(); // 1 - weight is over 55 kg
            $table->unsignedTinyInteger('no_contras')->nullable(); // 1 - no contraindications, can be accepted as donor
            $table->unsignedTinyInteger('blood_type_id')->nullable();

            $table->date('last_donorship_date')->nullable();

            $table->timestamps();

            $table->index(['birth_year']);
            $table->index(['weight_ok']);
            $table->index(['no_contras']);
            $table->index(['last_donorship_date']);
            $table->index(['blood_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donors');
    }
};
