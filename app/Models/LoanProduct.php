<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'sort_order',
        'stages',
    ];

    protected $casts = [
        'stages' => 'array',
    ];

    public function constitutions(): BelongsToMany
    {
        return $this->belongsToMany(CustomerConstitution::class, 'loan_product_constitutions', 'loan_product_id', 'constitution_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}
