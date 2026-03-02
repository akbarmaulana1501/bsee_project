<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAktivitasLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aktivitas_logs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal'); // tanggal aktivitas
            $table->string('aktivitas'); // nama aktivitas, ex: hauling, penambangan
            $table->float('produksi')->nullable(); // jumlah tonase
            $table->float('bbm')->nullable(); // liter
            $table->bigInteger('biaya')->nullable(); // rupiah
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aktivitas_logs');
    }
}
