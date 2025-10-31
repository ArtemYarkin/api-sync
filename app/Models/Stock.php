<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'is_supply',
        'is_realization',
        'quantity_full',
        'warehouse_name',
        'in_way_to_client',
        'in_way_from_client',
        'nm_id',
        'subject',
        'category',
        'brand',
        'sc_code',
        'price',
        'discount',
        'sync_date',
        'data'
    ];

    protected $casts = [
        'date' => 'date',
        'last_change_date' => 'datetime',
        'is_supply' => 'boolean',
        'is_realization' => 'boolean',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'sync_date' => 'date',
        'data' => 'array'
    ];
}