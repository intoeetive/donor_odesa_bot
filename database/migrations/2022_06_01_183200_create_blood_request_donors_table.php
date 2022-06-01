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
        Schema::create('blood_request_donors', function (Blueprint $table) {

            $table->foreignId('blood_request_id')
                ->constrained('blood_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('donor_id')
                ->constrained('donors')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_location');
    }
};
