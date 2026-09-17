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
        'bank_id',
        'assigned_employee_id',
        'assigned_by',
        'assigned_at',
        'loan_product_id',
        'constitution_id',
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
        'fssai_license',
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
        'land_and_factory_building',
        'connectivity',
        'required_loan_amount',
        'cc_amount',
        'cc_details',
        'term_loan_amount',
        'term_loan_machinery_details',
        'current_loans',
        'itr_details',
        'bank_loan_details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bank_details' => 'array',
        'current_loans' => 'array',
        'itr_details' => 'array',
        'bank_loan_details' => 'array',
        'itr_ay_2026_27' => 'boolean',
        'itr_ay_2025_26' => 'boolean',
        'itr_ay_2024_25' => 'boolean',
        'date_of_birth' => 'date',
        'assigned_at' => 'datetime',
    ];

    public function getFormattedItrDetailsAttribute(): array
    {
        if (!empty($this->itr_details) && is_array($this->itr_details)) {
            return $this->itr_details;
        }

        if ($this->itr_id || $this->itr_password || $this->itr_audited || $this->itr_ay_2026_27 || $this->itr_ay_2025_26 || $this->itr_ay_2024_25) {
            return [
                [
                    'itr_id' => $this->itr_id,
                    'itr_password' => $this->itr_password,
                    'itr_audited' => $this->itr_audited,
                    'itr_ay_2026_27' => $this->itr_ay_2026_27,
                    'itr_ay_2025_26' => $this->itr_ay_2025_26,
                    'itr_ay_2024_25' => $this->itr_ay_2024_25,
                ]
            ];
        }

        return [];
    }

    protected static function booted()
    {
        static::saving(function ($lead) {
            if ($lead->isDirty('constitution_id') && $lead->constitution_id) {
                $constitution = CustomerConstitution::find($lead->constitution_id);
                if ($constitution) {
                    $lead->constitution_of_business = $constitution->name;
                }
            }

            if (is_array($lead->itr_details) && count($lead->itr_details) > 0) {
                $first = $lead->itr_details[0];
                if (empty($lead->itr_id)) {
                    $lead->itr_id = $first['itr_id'] ?? null;
                }
                if (empty($lead->itr_password)) {
                    $lead->itr_password = $first['itr_password'] ?? null;
                }
                if (empty($lead->itr_audited)) {
                    $lead->itr_audited = $first['itr_audited'] ?? null;
                }
                $ay = $first['assessment_year'] ?? '';
                $lead->itr_ay_2026_27 = !empty($first['itr_ay_2026_27']) || str_contains($ay, '2026-27');
                $lead->itr_ay_2025_26 = !empty($first['itr_ay_2025_26']) || str_contains($ay, '2025-26');
                $lead->itr_ay_2024_25 = !empty($first['itr_ay_2024_25']) || str_contains($ay, '2024-25');
            }
        });

        static::created(function ($lead) {
            $lead->logActivity(
                auth()->id() ?? User::first()?->id ?? 1,
                'created',
                'Lead created'
            );
        });

        static::updated(function ($lead) {
            if ($lead->isDirty('status')) {
                $oldStatus = $lead->getOriginal('status');
                $newStatus = $lead->status;
                $lead->logActivity(
                    auth()->id() ?? User::first()?->id ?? 1,
                    'status_changed',
                    "Status changed from '" . ucfirst($oldStatus) . "' to '" . ucfirst($newStatus) . "'"
                );
            }
        });
    }

    /**
     * Log an activity on this lead.
     */
    public function logActivity($userId, string $type, string $description): LeadActivity
    {
        return $this->activities()->create([
            'user_id' => $userId,
            'type' => $type,
            'description' => $description,
        ]);
    }

    /**
     * Assign lead to an employee.
     */
    public function assignTo($employeeId, $assignedBy, $notes = null): void
    {
        $oldEmployeeId = $this->assigned_employee_id;
        
        $this->update([
            'assigned_employee_id' => $employeeId,
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
        ]);

        $this->assignments()->create([
            'employee_id' => $employeeId,
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
            'notes' => $notes,
        ]);

        $employee = User::find($employeeId);
        $employeeName = $employee ? $employee->name : 'Unknown';

        if ($oldEmployeeId) {
            $this->logActivity($assignedBy, 'reassigned', "Lead reassigned to {$employeeName}");
        } else {
            $this->logActivity($assignedBy, 'assigned', "Lead assigned to {$employeeName}");
        }
    }

    /**
     * Get the bank assigned to the lead.
     */
    public function bank(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get documents associated with the lead.
     */
    public function leadDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadDocument::class);
    }

    /**
     * Get the loan product requested by the lead.
     */
    public function loanProduct(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    /**
     * Get the customer constitution of the lead.
     */
    public function constitution(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerConstitution::class);
    }

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

    /**
     * Get the assigned employee.
     */
    public function assignedEmployee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    /**
     * Get the user who assigned the lead.
     */
    public function assigner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get all assignments for this lead.
     */
    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadAssignment::class);
    }

    /**
     * Get all visits for this lead.
     */
    public function visits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Get all activities for this lead.
     */
    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }
}
