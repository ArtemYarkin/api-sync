<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'income_id',
        'number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'total_price',
        'date_close',
        'warehouse_name',
        'nm_id',
        'data'
    ];

    protected $casts = [
        'date' => 'datetime',
        'last_change_date' => 'datetime',
        'total_price' => 'decimal:2',
        'date_close' => 'datetime',
        'data' => 'array'
    ];
}