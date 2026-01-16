<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'province',
        'city',
        'features',
        'owner_id',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2'
    ];



    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }



    public function images()
    {
        return $this->hasMany(ApartmentImage::class);
    }



    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }



    public function reviews()
    {
        return $this->hasMany(Review::class);
    }



    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }



    public function scopeInProvince($query, $province)
    {
        return $query->where('province', $province);
    }



    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }



    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }



    public function scopeHasFeatures($query, array $features)
    {
        return $query->where(function($q) use ($features) {
            foreach ($features as $feature) {
                $q->orWhereJsonContains('features', trim($feature));
            }
        });
    }
}
