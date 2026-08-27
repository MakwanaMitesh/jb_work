<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerConstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'sort_order',
    ];

    public function loanProducts(): BelongsToMany
    {
        return $this->belongsToMany(LoanProduct::class, 'loan_product_constitutions', 'constitution_id', 'loan_product_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}
