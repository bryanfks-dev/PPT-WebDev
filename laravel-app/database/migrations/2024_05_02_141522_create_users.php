<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integerIncrements('User_ID')->primary();
            $table->string('Full_Name');
            $table->string('Email')->unique();
            $table->string('Password');
            $table->date('Date_of_Birth');
            $table->string('Address');
            $table->string('NIK', 16);
            $table->string('Photo');
            $table->enum('Gender', ['Male', 'Female']);
            $table->string('Phone_Number', 13);
            $table->tinyInteger('Department_ID')->unsigned()->nullable();
            $table->date('First_Login')->nullable();

            $table->foreign('Department_ID')->references('Department_ID')
                ->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
