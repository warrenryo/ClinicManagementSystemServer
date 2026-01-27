<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItems extends Model
{
    protected $table = 'purchase_order_items';
    protected $fillable = ['purchase_order_id', 'product_id', 'quantity'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function products()
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }
}
