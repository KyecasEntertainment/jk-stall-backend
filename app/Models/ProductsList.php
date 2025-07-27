<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsList extends Model
{
    use SoftDeletes;
    protected $table = 'products_lists';

    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'product_name',
    ];

    public function stockBatches()
    {
        return $this->hasMany(StockBatches::class, 'product_id', 'product_id');
    }

    public function dailyStockActivities()
    {
        return $this->hasMany(DailyStockActivity::class, 'product_id', 'product_id');
    }
}
