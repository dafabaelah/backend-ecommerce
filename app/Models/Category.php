<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'image'
    ];
    
    /**
     * products
     *
     * @return void
     */

    // mendefinisikan relasi one-to-many dengan model Product
    // relasi dari tabel category ke tabel product
    // one to many adalah satu category bisa memiliki banyak product
    // inverse dari relasi many-to-one pada model Product
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
