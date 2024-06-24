<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// membuat model Customer dengan perintah php artisan make:model Customer -m
// flag "-m" untuk membuat migration

class Customer extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */

    // fillable adalah properti yang digunakan untuk menentukan field mana saja yang boleh diisi
    
    protected $fillable = [
        'name', 'email', 'email', 'email_verified_at', 'password', 'remember_token'
    ];
    
    /**
     * invoices
     *
     * @return void
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
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
