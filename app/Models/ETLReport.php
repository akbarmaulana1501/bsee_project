<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ETLReport extends Model
{
    use HasFactory;

    protected $table = 'e_t_l_reports';

    protected $fillable = [
        'periode',
        'tonnage_coal',
        'price_per_ton_usd',
        'price_total_usd',
        'total_rp',
        'pendapatan',
    ];
}
