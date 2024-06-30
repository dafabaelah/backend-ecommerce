<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable; // <-- import Auth Laravel

// membuat model Customer dengan perintah php artisan make:model Customer -m
// flag "-m" untuk membuat migration

class Customer extends Authenticatable implements JWTSubject
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

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => \Carbon\Carbon::parse($value)->translatedFormat('l, d F Y'),
        );
    }
    
    
    /**
     * getJWTIdentifier
     *
     * @return void
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    /**
     * getJWTCustomClaims
     *
     * @return void
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
