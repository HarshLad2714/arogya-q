<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClinicResource;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClinicApiController extends Controller
{
    /**
     * GET /api/v1/clinics?q=&specialty=
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $clinics = Clinic::query()
            ->approved()
            ->withAvg('reviews', 'rating')
            ->when($request->filled('specialty'), fn ($q) => $q->where('specialty', $request->string('specialty')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->paginate(12);

        return ClinicResource::collection($clinics);
    }

    public function show(Clinic $clinic): ClinicResource
    {
        abort_unless($clinic->status->value === 'approved', 404);
        $clinic->load(['doctors.user'])->loadAvg('reviews', 'rating');
        $clinic->doctors->loadAvg('reviews', 'rating');

        return new ClinicResource($clinic);
    }
}
