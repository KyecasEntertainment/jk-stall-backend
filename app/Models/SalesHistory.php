<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesHistory extends Model
{
    protected $table = 'sales_histories';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity_sold',
    ];

    public function product()
    {
        return $this->belongsTo(ProductsList::class, 'product_id', 'product_id');
    }
}
