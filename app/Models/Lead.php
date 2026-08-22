<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile_number',
        'alternate_mobile_number',
        'agent_id',
        'city_id',
        'source',
        'status',
        'notes',
        'date_of_birth',
        'gender',
        'address',
        'aadhar_card',
        'pan_card',
        'udyam_registration',
        'education',
        'mother_name',
        'itr_id',
        'itr_password',
        'itr_audited',
        'itr_ay_2026_27',
        'itr_ay_2025_26',
        'itr_ay_2024_25',
        'bank_details',
        'business_name',
        'constitution_of_business',
        'introduction',
        'business_address',
        'gst_applicable',
        'gst_number',
        'gst_id',
        'gst_password',
        'firm_name',
        'business_activity',
        'business_experience',
        'no_of_manpower',
        'business_location',
        'area_of_premises',
        'connectivity',
        'required_loan_amount',
        'cc_amount',
        'cc_details',
        'term_loan_amount',
        'term_loan_machinery_details',
        'current_loans',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bank_details' => 'array',
        'current_loans' => 'array',
        'itr_ay_2026_27' => 'boolean',
        'itr_ay_2025_26' => 'boolean',
        'itr_ay_2024_25' => 'boolean',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the agent assigned to the lead.
     */
    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the city this lead is from.
     */
    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
