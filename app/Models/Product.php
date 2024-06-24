<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'image', 'title', 'slug', 'category_id', 'user_id', 'description', 'weight', 'price', 'stock', 'discount'
    ];
    
    /**
     * category
     *
     * @return void
     */

    // mendefinisikan relasi many-to-one dengan model Category
    // relasi dari tabel product ke tabel category
    // many to one adalah banyak product bisa dimiliki oleh satu category
    // inverse dari relasi one-to-many pada model Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * reviews
     *
     * @return void
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
