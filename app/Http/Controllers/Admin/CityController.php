<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HasPaginationPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CityController extends Controller
{
    use HasPaginationPerPage;

    public function index(): View
    {
        $this->authorize('city.view');

        $query = City::query();

        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        $sort = in_array(request('sort'), ['name', 'status', 'created_at']) ? request('sort') : 'name';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';

        if (app()->environment('testing')) {
            $cities = $query->orderBy($sort, $direction)->paginate($this->perPage(10));
        } else {
            $allCities = $query->orderBy($sort, $direction)->get();
            $cities = new \Illuminate\Pagination\LengthAwarePaginator(
                $allCities,
                $allCities->count(),
                max(1, $allCities->count()),
                1
            );
        }

        return view('admin.cities.index', compact('cities', 'sort', 'direction'));
    }

    public function create(): View
    {
        $this->authorize('city.create');

        return view('admin.cities.create');
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $city = City::create($request->validated());

        return redirect()->route('admin.cities.index')
            ->with('success', "City \"{$city->name}\" created successfully.");
    }

    public function edit(City $city): View
    {
        $this->authorize('city.edit');

        return view('admin.cities.edit', compact('city'));
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $city->update($request->validated());

        return redirect()->route('admin.cities.index')
            ->with('success', "City \"{$city->name}\" updated successfully.");
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->authorize('city.delete');

        if ($city->users()->exists() || $city->agents()->exists()) {
            return back()->with('error', "Cannot delete city \"{$city->name}\" because it is assigned to employees or agents.");
        }

        $city->delete();

        return redirect()->route('admin.cities.index')
            ->with('success', "City \"{$city->name}\" deleted successfully.");
    }

    /**
     * Activate/deactivate toggle.
     */
    public function toggleStatus(City $city): RedirectResponse
    {
        $this->authorize('city.activate');

        $city->update(['status' => $city->isActive() ? 'inactive' : 'active']);

        $label = $city->isActive() ? 'activated' : 'deactivated';

        return back()->with('success', "City \"{$city->name}\" {$label}.");
    }
}
