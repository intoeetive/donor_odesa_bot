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
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedTinyInteger('blood_type_id');

            $table->unsignedInteger('qty');

            $table->date('closed_on')->nullable();

            $table->timestamps();

            $table->index(['blood_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blood_requests');
    }
};
