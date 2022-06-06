<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donor_blood_request_responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('donor_id')
                ->nullable()
                ->constrained('donors')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('blood_request_id')
                ->nullable()
                ->constrained('blood_requests')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->unsignedTinyInteger('no_response_contras')->nullable(); // 1 - no contraindications, can be accepted as donor

            $table->dateTime('confirmation_date')->nullable();
            $table->dateTime('donorship_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('donor_blood_request_reponses');
    }
};
