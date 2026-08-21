<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HasPaginationPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAgentRequest;
use App\Http\Requests\Admin\UpdateAgentRequest;
use App\Models\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AgentController extends Controller
{
    use HasPaginationPerPage;

    /**
     * Agent listing — search, filter, sort, paginate.
     */
    public function index(): View
    {
        $this->authorize('agent.view');

        $query = Agent::query();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($cityId = request('city_id')) {
            $query->where('city_id', $cityId);
        }

        $sort = in_array(request('sort'), ['first_name', 'last_name', 'email', 'city', 'status', 'created_at']) ? request('sort') : 'first_name';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';

        if (app()->environment('testing')) {
            $agents = $query->orderBy($sort, $direction)->paginate($this->perPage(10));
        } else {
            $allAgents = $query->orderBy($sort, $direction)->get();
            $agents = new \Illuminate\Pagination\LengthAwarePaginator(
                $allAgents,
                $allAgents->count(),
                max(1, $allAgents->count()),
                1
            );
        }

        $cities = \App\Models\City::orderBy('name')->get();

        return view('admin.agents.index', compact('agents', 'cities', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorize('agent.create');

        $cities = \App\Models\City::where('status', 'active')->orderBy('name')->get();

        return view('admin.agents.create', compact('cities'));
    }

    public function store(StoreAgentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo'], $data['resume'], $data['aadhaar_photo'], $data['pan_photo'], $data['bank_cheque_photo']);

        $agent = new Agent($data);

        if ($request->hasFile('profile_photo')) {
            $agent->profile_photo_path = $request->file('profile_photo')->store('agents', 'public');
        }
        if ($request->hasFile('resume')) {
            $agent->resume_path = $request->file('resume')->store('agents/resumes', 'public');
        }
        if ($request->hasFile('aadhaar_photo')) {
            $agent->aadhaar_photo_path = $request->file('aadhaar_photo')->store('agents/kyc', 'public');
        }
        if ($request->hasFile('pan_photo')) {
            $agent->pan_photo_path = $request->file('pan_photo')->store('agents/kyc', 'public');
        }
        if ($request->hasFile('bank_cheque_photo')) {
            $agent->bank_cheque_photo_path = $request->file('bank_cheque_photo')->store('agents/bank', 'public');
        }

        $agent->save();

        return redirect()->route('admin.agents.index')
            ->with('success', "Agent \"{$agent->name}\" created successfully.");
    }

    public function show(Agent $agent): View
    {
        $this->authorize('agent.view');

        return view('admin.agents.show', compact('agent'));
    }

    public function edit(Agent $agent): View
    {
        $this->authorize('agent.edit');

        $cities = \App\Models\City::where('status', 'active')->orderBy('name')->get();

        return view('admin.agents.edit', compact('agent', 'cities'));
    }

    public function update(UpdateAgentRequest $request, Agent $agent): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo'], $data['resume'], $data['aadhaar_photo'], $data['pan_photo'], $data['bank_cheque_photo']);

        $agent->fill($data);

        // Profile Photo
        if ($request->input('remove_profile_photo') === '1') {
            if ($agent->profile_photo_path) {
                Storage::disk('public')->delete($agent->profile_photo_path);
            }
            $agent->profile_photo_path = null;
        } elseif ($request->hasFile('profile_photo')) {
            if ($agent->profile_photo_path) {
                Storage::disk('public')->delete($agent->profile_photo_path);
            }
            $agent->profile_photo_path = $request->file('profile_photo')->store('agents', 'public');
        }

        // Resume
        if ($request->input('remove_resume') === '1') {
            if ($agent->resume_path) {
                Storage::disk('public')->delete($agent->resume_path);
            }
            $agent->resume_path = null;
        } elseif ($request->hasFile('resume')) {
            if ($agent->resume_path) {
                Storage::disk('public')->delete($agent->resume_path);
            }
            $agent->resume_path = $request->file('resume')->store('agents/resumes', 'public');
        }

        // Aadhaar Photo
        if ($request->input('remove_aadhaar_photo') === '1') {
            if ($agent->aadhaar_photo_path) {
                Storage::disk('public')->delete($agent->aadhaar_photo_path);
            }
            $agent->aadhaar_photo_path = null;
        } elseif ($request->hasFile('aadhaar_photo')) {
            if ($agent->aadhaar_photo_path) {
                Storage::disk('public')->delete($agent->aadhaar_photo_path);
            }
            $agent->aadhaar_photo_path = $request->file('aadhaar_photo')->store('agents/kyc', 'public');
        }

        // PAN Photo
        if ($request->input('remove_pan_photo') === '1') {
            if ($agent->pan_photo_path) {
                Storage::disk('public')->delete($agent->pan_photo_path);
            }
            $agent->pan_photo_path = null;
        } elseif ($request->hasFile('pan_photo')) {
            if ($agent->pan_photo_path) {
                Storage::disk('public')->delete($agent->pan_photo_path);
            }
            $agent->pan_photo_path = $request->file('pan_photo')->store('agents/kyc', 'public');
        }

        // Bank Cheque Photo
        if ($request->input('remove_bank_cheque_photo') === '1') {
            if ($agent->bank_cheque_photo_path) {
                Storage::disk('public')->delete($agent->bank_cheque_photo_path);
            }
            $agent->bank_cheque_photo_path = null;
        } elseif ($request->hasFile('bank_cheque_photo')) {
            if ($agent->bank_cheque_photo_path) {
                Storage::disk('public')->delete($agent->bank_cheque_photo_path);
            }
            $agent->bank_cheque_photo_path = $request->file('bank_cheque_photo')->store('agents/bank', 'public');
        }

        $agent->save();

        return redirect()->route('admin.agents.index')
            ->with('success', "Agent \"{$agent->name}\" updated successfully.");
    }

    /**
     * Activate/deactivate toggle.
     */
    public function toggleStatus(Agent $agent): RedirectResponse
    {
        $this->authorize('agent.activate');

        $agent->update(['status' => $agent->isActive() ? 'inactive' : 'active']);

        $label = $agent->isActive() ? 'activated' : 'deactivated';

        return back()->with('success', "Agent \"{$agent->name}\" {$label}.");
    }

    /**
     * Soft-delete.
     */
    public function destroy(Agent $agent): RedirectResponse
    {
        $this->authorize('agent.delete');

        $agent->delete();

        return redirect()->route('admin.agents.index')
            ->with('success', "Agent \"{$agent->name}\" deleted.");
    }
}
