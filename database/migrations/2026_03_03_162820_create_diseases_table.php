<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('diseases', function (Blueprint $table) {
            $table->id();
            $table->string('disease_name');
            $table->string('bangla_name')->nullable();
            $table->string('disease_name_bn')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diseases');
    }
};