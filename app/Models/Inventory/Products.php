<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'title',
        'description',
        'uom',
        'at_cost',
        'reflenish_amount',
        'quantity',
    ];
}
