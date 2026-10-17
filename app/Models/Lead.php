<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_VISIT_PENDING = 'visit_pending';
    public const STATUS_VISIT_COMPLETED = 'visit_completed';
    public const STATUS_DOC_PENDING = 'documentation_pending';
    public const STATUS_DOC_IN_PROGRESS = 'documentation_in_progress';
    public const STATUS_DOC_COMPLETED = 'documentation_completed';
    public const STATUS_UNDER_PROCESS = 'under_process';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_LOST = 'lost';

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_VISIT_PENDING => 'Visit Pending',
        self::STATUS_VISIT_COMPLETED => 'Visit Completed',
        self::STATUS_DOC_PENDING => 'Documentation Pending',
        self::STATUS_DOC_IN_PROGRESS => 'Documentation In Progress',
        self::STATUS_DOC_COMPLETED => 'Documentation Completed',
        self::STATUS_UNDER_PROCESS => 'Under Process',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_CONVERTED => 'Converted',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_LOST => 'Lost',
    ];

    public const PENDING_ACTIVE_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_VISIT_PENDING,
        self::STATUS_VISIT_COMPLETED,
        self::STATUS_DOC_PENDING,
        self::STATUS_DOC_IN_PROGRESS,
        self::STATUS_DOC_COMPLETED,
        self::STATUS_UNDER_PROCESS,
    ];

    public const APPROVED_CONVERTED_STATUSES = [
        self::STATUS_APPROVED,
        self::STATUS_CONVERTED,
        self::STATUS_COMPLETED,
    ];

    public const REJECTED_CLOSED_STATUSES = [
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_LOST,
    ];

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
        'place_of_birth',
        'occupation',
        'gender',
        'address',
        'aadhar_card',
        'pan_card',
        'udyam_registration',
        'fssai_license',
        'education',
        'mother_name',
        'father_full_name',
        'relationship',
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
     * Check if Pre-Sanction Inspection Sheet is applicable for this lead.
     * Applicable ONLY when Bank is SBI (State Bank of India) AND Loan Product is Business Loan.
     */
    public function isInspectionSheetApplicable(): bool
    {
        $bankName = strtolower(trim($this->bank?->name ?? ''));
        $isSbi = str_contains($bankName, 'sbi') || str_contains($bankName, 'state bank');

        $loanProductName = strtolower(trim($this->loanProduct?->name ?? ''));
        $loanProductCode = strtolower(trim($this->loanProduct?->code ?? ''));
        $isBusinessLoan = str_contains($loanProductName, 'business') || $loanProductCode === 'bl';

        return $isSbi && $isBusinessLoan;
    }

    public function hasGeneratedCma(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->exists('cma/' . $this->id . '/CMA.xlsx');
    }

    public function hasGeneratedDpr(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->exists('dpr/' . $this->id . '/DPR.docx');
    }

    public function hasGeneratedOr(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->exists('or/' . $this->id . '/OR.docx');
    }

    public function hasGeneratedSbiStockStatement(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->exists('sbi_stock_statement/' . $this->id . '/SBI_Stock_statement.xlsx');
    }

    public function hasGeneratedInspectionSheet(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->exists('inspection_sheets/' . $this->id . '/inspection_sheet_lead_' . $this->id . '.pdf');
    }

    /**
     * Get the bank assigned to the lead.
     */
    public function applicationForm(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ApplicationForm::class);
    }

    public function dprReport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DprReport::class);
    }

    public function cmaProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CmaProfile::class);
    }

    public function opinionReport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OpinionReport::class);
    }

    public function stockStatement(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StockStatement::class);
    }

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
