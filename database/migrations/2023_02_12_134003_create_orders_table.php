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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_pemesan');
            $table->string('no_telp_pemesan');
            $table->text('alamat_pemesan');
            $table->timestamp('tanggal_pemesanan');
            $table->date('tanggal_acara');
            $table->time('waktu_acara');
            $table->time('waktu_selesai_acara');
            $table->text('lokasi_acara');
            $table->enum('status',['Menunggu Persetujuan','Ditolak','Belum DP','Book is Pending','Date Book is Pending','Pelunasan is Pending','Menunggu Pelunasan','Diproses','Dibatalkan', 'Selesai','Booked','Tanggal Booked'])->default('Menunggu Persetujuan');
            $table->string('pending_url')->nullable();
            $table->enum('dinilai',[0,1])->default(0);
            $table->integer('total');
            $table->text('catatan')->default(null);
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
        Schema::dropIfExists('orders');
    }
};
