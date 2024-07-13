<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
    
    /**
     * image
     *
     * @return Attribute
     */

    // accessor adalah method yang digunakan untuk mengubah nilai dari field tertentu sebelum ditampilkan ke user
    // method image() digunakan untuk mengubah nilai dari field image sebelum ditampilkan ke user
    // method ini akan mengembalikan url dari image yang disimpan di storage
    
    // protected function image(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => url('/storage/categories/' . $value),
    //     );
    // }
}
