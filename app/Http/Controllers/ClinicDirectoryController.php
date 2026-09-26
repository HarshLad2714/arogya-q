<?php

namespace App\Http\Controllers;

use App\Enums\ClinicStatus;
use App\Enums\UserRole;
use App\Models\Clinic;
use App\Services\QueueEngineService;
use App\Support\Geo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ClinicDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'specialty' => ['nullable', 'string', 'max:80'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        $query = Clinic::query()
            ->approved()
            ->withCount('doctors')
            ->withAvg('reviews', 'rating');

        if (! empty($filters['specialty'])) {
            $query->where('specialty', $filters['specialty']);
        }

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('specialty', 'like', $term);
            });
        }

        $items = $query->get()->map(function (Clinic $clinic) use ($filters) {
            $clinic->distance_km = Geo::kilometers(
                isset($filters['lat']) ? (float) $filters['lat'] : null,
                isset($filters['lng']) ? (float) $filters['lng'] : null,
                $clinic->latitude,
                $clinic->longitude,
            );

            return $clinic;
        });

        if ($request->filled('lat') && $request->filled('lng')) {
            $items = $items->sortBy('distance_km')->values();
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $clinics = new LengthAwarePaginator(
            $items->forPage($page, 9)->values(),
            $items->count(),
            9,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('clinics.index', [
            'clinics' => $clinics,
            'specialties' => config('arogya.specialties'),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Clinic $clinic, QueueEngineService $queue): View
    {
        $user = $request->user();
        $canPreview = $user && (
            $user->role === UserRole::SuperAdmin || $user->id === $clinic->admin_id
        );

        abort_unless($clinic->status === ClinicStatus::Approved || $canPreview, 404);

        $doctors = $clinic->doctors()
            ->with(['user', 'schedules', 'leaves' => fn ($q) => $q->whereDate('leave_date', '>=', today())->orderBy('leave_date')])
            ->withAvg('reviews', 'rating')
            ->when(! $canPreview, fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(function ($doctor) use ($queue) {
                $doctor->live = $queue->status($doctor->id, now()->toDateString());

                return $doctor;
            });

        $clinic->loadAvg('reviews', 'rating');
        $clinic->load(['reviews' => fn ($q) => $q->with('patient')->latest()->limit(6)]);

        return view('clinics.show', [
            'clinic' => $clinic,
            'doctors' => $doctors,
        ]);
    }
}
