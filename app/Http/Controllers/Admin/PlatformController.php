<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Services\AnalyticsService;
use App\Services\ClinicService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function dashboard(AnalyticsService $analytics): View
    {
        return view('platform.dashboard', [
            'stats' => $analytics->platform(),
            'pending' => Clinic::query()->with('admin')->where('status', 'pending')->latest()->limit(5)->get(),
        ]);
    }

    public function clinics(Request $request): View
    {
        $status = $request->string('status')->toString();
        $clinics = Clinic::query()
            ->with('admin')
            ->withCount('doctors')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('platform.clinics', compact('clinics', 'status'));
    }

    public function approve(Clinic $clinic, ClinicService $clinics): RedirectResponse
    {
        $this->authorize('approve', $clinic);
        $clinics->approve($clinic);

        return back()->with('status', __('ui.platform.approved'));
    }

    public function reject(Request $request, Clinic $clinic, ClinicService $clinics): RedirectResponse
    {
        $this->authorize('approve', $clinic);
        $data = $request->validate(['reason' => ['required', 'string', 'max:300']]);
        $clinics->reject($clinic, $data['reason']);

        return back()->with('status', __('ui.platform.rejected'));
    }
}
