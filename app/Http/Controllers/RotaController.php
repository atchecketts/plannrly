<?php

namespace App\Http\Controllers;

use App\Enums\RotaStatus;
use App\Http\Requests\Rota\StoreRotaRequest;
use App\Http\Requests\Rota\UpdateRotaRequest;
use App\Models\Department;
use App\Models\Location;
use App\Models\Rota;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RotaController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Rota::class);

        $rotas = Rota::with(['location', 'department', 'createdBy'])
            ->withCount('shifts')
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('rotas.index', compact('rotas'));
    }

    public function create(): View
    {
        $this->authorize('create', Rota::class);

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with('location')->active()->orderBy('name')->get();

        return view('rotas.create', compact('locations', 'departments'));
    }

    public function store(StoreRotaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by'] = auth()->id();
        $data['status'] = RotaStatus::Draft;

        $rota = Rota::create($data);

        return redirect()
            ->route('rotas.show', $rota)
            ->with('success', 'Rota created successfully.');
    }

    public function show(Rota $rota): View
    {
        $this->authorize('view', $rota);

        $rota->load([
            'shifts.user',
            'shifts.department',
            'shifts.businessRole',
            'location',
            'department',
        ]);

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with('location')->active()->orderBy('name')->get();

        return view('rotas.show', compact('rota', 'locations', 'departments'));
    }

    public function edit(Rota $rota): View
    {
        $this->authorize('update', $rota);

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with('location')->active()->orderBy('name')->get();

        return view('rotas.edit', compact('rota', 'locations', 'departments'));
    }

    public function update(UpdateRotaRequest $request, Rota $rota): RedirectResponse
    {
        $rota->update($request->validated());

        return redirect()
            ->route('rotas.show', $rota)
            ->with('success', 'Rota updated successfully.');
    }

    public function destroy(Rota $rota): RedirectResponse
    {
        $this->authorize('delete', $rota);

        $rota->delete();

        return redirect()
            ->route('rotas.index')
            ->with('success', 'Rota deleted successfully.');
    }

    public function publish(Rota $rota): RedirectResponse
    {
        $this->authorize('publish', $rota);

        $rota->publish(auth()->user());

        return redirect()
            ->route('rotas.show', $rota)
            ->with('success', 'Rota published successfully.');
    }
}
