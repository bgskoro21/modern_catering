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
        Schema::create('sub_menu_prasmanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_prasmanan_id')->constrained('menu_prasmanans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('sub_menu');
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
        Schema::dropIfExists('sub_menu_prasmanans');
    }
};
