<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanProductConstitution extends Model
{
    use HasFactory;

    protected $table = 'loan_product_constitutions';

    protected $fillable = [
        'loan_product_id',
        'constitution_id',
        'status',
    ];

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function constitution(): BelongsTo
    {
        return $this->belongsTo(CustomerConstitution::class);
    }
}
