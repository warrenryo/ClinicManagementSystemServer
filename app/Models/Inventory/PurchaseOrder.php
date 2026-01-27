<?php

namespace App\Models\Inventory;

use App\Models\Auth\UserDetails;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';

    protected $fillable = [
        'created_by_id',
        'notes',
        'approval_status',
        'reject_reason',
    ];

    public function userDetails()
    {
        return $this->belongsTo(UserDetails::class, 'created_by_id', 'id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItems::class, 'purchase_order_id', 'id');
    }
}
