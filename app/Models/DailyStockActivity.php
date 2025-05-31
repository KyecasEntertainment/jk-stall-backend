<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockActivity extends Model
{
    protected $table = 'daily_stock_activities';

    protected $fillable = [
        'activity_id',
        'product_id',
        'date',
        'displayed_quantity',
        'returned_quantity',
        'sold_quantity',
        'notes'
    ];

    public function stockBatch()
    {
        return $this->belongsTo(ProductsList::class, 'product_id', 'product_id');
    }
}
