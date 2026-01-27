<?php

namespace App\Models\Inventory;

use App\Helpers\SearchableTrait;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use SearchableTrait;
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
