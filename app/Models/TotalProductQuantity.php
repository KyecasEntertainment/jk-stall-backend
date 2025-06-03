<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalProductQuantity extends Model
{
    protected $table = 'total_product_quantities';

    protected $fillable = [
        'product_id',
        'all_total_quantity',
        'current_total_quantity',
        'total_displayed_quantity',
        'sold_quantity',
        'total_discarded_quantity',
    ];

    public function product()
    {
        return $this->belongsTo(ProductsList::class, 'product_id', 'product_id');
    }
}
