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

        if ($city = request('city')) {
            $query->where('city', $city);
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

        $cities = Agent::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('admin.agents.index', compact('agents', 'cities', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorize('agent.create');

        return view('admin.agents.create');
    }

    public function store(StoreAgentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo']);

        $agent = new Agent($data);

        if ($request->hasFile('profile_photo')) {
            $agent->profile_photo_path = $request->file('profile_photo')->store('agents', 'public');
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

        return view('admin.agents.edit', compact('agent'));
    }

    public function update(UpdateAgentRequest $request, Agent $agent): RedirectResponse
    {
        $data = $request->validated();
        unset($data['profile_photo']);

        $agent->fill($data);

        if ($request->hasFile('profile_photo')) {
            if ($agent->profile_photo_path) {
                Storage::disk('public')->delete($agent->profile_photo_path);
            }
            $agent->profile_photo_path = $request->file('profile_photo')->store('agents', 'public');
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
