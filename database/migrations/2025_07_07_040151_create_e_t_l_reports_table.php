<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateETLReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('e_t_l_reports', function (Blueprint $table) {
            $table->id();
            $table->string('periode');
            $table->double('tonnage_coal')->nullable();
            $table->double('price_per_ton_usd')->nullable();
            $table->double('price_total_usd')->nullable();
            $table->double('total_rp')->nullable();
            $table->double('pendapatan')->nullable();
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
        Schema::dropIfExists('e_t_l_reports');
    }
}
