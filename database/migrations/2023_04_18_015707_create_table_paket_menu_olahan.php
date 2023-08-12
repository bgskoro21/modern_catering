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
        Schema::create('paket_menu_olahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_prasmanan_id')->constrained('paket_prasmanans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('menu_prasmanan_id')->constrained('menu_prasmanans')->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('table_paket_menu_olahans');
    }
};
