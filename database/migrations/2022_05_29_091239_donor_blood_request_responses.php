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
        Schema::create('donor_blood_request_reponses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('donor_id')->constrained('donors')->nullable()->nullOnDelete();
            $table->foreignId('location_id')->constrained('locations')->nullable()->nullOnDelete();
            $table->foreignId('blood_request_id')->constrained('blood_requests')->nullable()->nullOnDelete();

            $table->unsignedTinyInteger('no_response_contras')->nullable(); // 1 - no contraindications, can be accepted as donor

            $table->date('confirmation_date')->nullable();
            $table->date('donorship_date')->nullable();

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
        //
    }
};
