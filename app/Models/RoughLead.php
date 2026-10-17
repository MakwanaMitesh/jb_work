<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoughLead extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'interested' => 'Interested',
        'follow_up' => 'Follow-up',
        'qualified' => 'Qualified',
        'not_interested' => 'Not Interested',
        'converted' => 'Converted',
        'closed' => 'Closed',
    ];

    protected $fillable = [
        'full_name',
        'mobile_number',
        'alternate_mobile_number',
        'email',
        'city_id',
        'loan_requirement',
        'description',
        'source',
        'assigned_employee_id',
        'status',
        'converted_lead_id',
        'converted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    public function convertedLead()
    {
        return $this->belongsTo(Lead::class, 'converted_lead_id');
    }

    public function isConverted(): bool
    {
        return ! is_null($this->converted_lead_id);
    }
}
