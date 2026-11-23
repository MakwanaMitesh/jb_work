<?php

namespace App\Http\Requests\Admin;

use App\Models\LeadWorkflowStage;
use App\Models\Role;
use App\Support\LeadTabs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveWorkflowStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Role::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'role_id' => [
                'required', 'integer',
                // Admin already sees everything and Agent can't log in — neither can work a stage.
                Rule::exists('roles', 'id')->where(fn ($q) => $q->whereNotIn('name', ['Admin', 'Agent'])),
            ],
            'sequence' => ['nullable', 'integer', 'min:1', 'max:999'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['string', Rule::in(LeadTabs::keys())],
            'view_modules' => ['nullable', 'array'],
            'view_modules.*' => ['string', Rule::in(LeadTabs::keys())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'modules.required' => 'Select at least one tab this stage fills in.',
            'role_id.exists' => 'Choose a staff role (Admin and Agent cannot be assigned to a stage).',
        ];
    }

    /**
     * A tab is filled in by exactly one stage — otherwise two roles would own the same data and
     * the "complete before transfer" check would hold the lead up twice.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $current = $this->route('stage');
                $taken = LeadWorkflowStage::query()
                    ->when($current instanceof LeadWorkflowStage, fn ($q) => $q->whereKeyNot($current->id))
                    ->get(['name', 'modules']);

                foreach ((array) $this->input('modules', []) as $key) {
                    $owner = $taken->first(fn ($s) => in_array($key, $s->modules ?? [], true));
                    if ($owner) {
                        $validator->errors()->add('modules', LeadTabs::label($key) . " is already filled in by the '{$owner->name}' stage.");
                    }
                }
            },
        ];
    }
}
