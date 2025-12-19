<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WithdrawalRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'renter_id',
        'amount',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2'
    ];

    /**
     * Get the renter that owns the withdrawal request.
     */
    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }
}
