<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasLog extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_logs';

    protected $fillable = [
        'tanggal', 'aktivitas', 'produksi', 'bbm', 'biaya'
    ];
}
