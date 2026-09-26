<?php

namespace App\Http\Controllers\ClinicAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorStoreRequest;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\Review;
use App\Services\AnalyticsService;
use App\Services\ClinicService;
use App\Services\DoctorService;
use App\Services\ReportService;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PanelController extends Controller
{
    public function dashboard(AnalyticsService $analytics): View
    {
        $clinic = $this->clinic();

        return view('clinic.dashboard', [
            'clinic' => $clinic,
            'stats' => $analytics->clinic($clinic),
        ]);
    }

    public function editProfile(): View
    {
        return view('clinic.profile', [
            'clinic' => $this->clinic(),
            'specialties' => config('arogya.specialties'),
        ]);
    }

    public function updateProfile(Request $request, ClinicService $clinics): RedirectResponse
    {
        $clinic = $this->clinic();
        $this->authorize('update', $clinic);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'specialty' => ['required', Rule::in(config('arogya.specialties'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:20'],
            'services' => ['nullable', 'string', 'max:400'],
            'cancel_cutoff_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'refund_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $clinics->update($clinic, collect($data)->except('document')->all(), $request->file('document'));

        return back()->with('status', __('ui.common.saved'));
    }

    public function doctors(): View
    {
        $clinic = $this->clinic();

        return view('clinic.doctors.index', [
            'clinic' => $clinic,
            'doctors' => $clinic->doctors()->with(['user', 'schedules', 'leaves'])->get(),
            'receptionists' => $clinic->admin->clinic?->id
                ? \App\Models\User::query()->where('clinic_id', $clinic->id)->where('role', 'receptionist')->get()
                : collect(),
        ]);
    }

    public function createDoctor(): View
    {
        return view('clinic.doctors.form', [
            'doctor' => null,
            'clinic' => $this->clinic(),
        ]);
    }

    public function storeDoctor(DoctorStoreRequest $request, DoctorService $doctors): RedirectResponse
    {
        $rows = $this->scheduleRows($request);

        if ($rows === []) {
            return back()->withInput()->withErrors(['schedules' => __('ui.panel.schedule_required')]);
        }

        $doctors->create($this->clinic(), $request->validated(), $rows);

        return redirect()->route('clinic.doctors')->with('status', __('ui.panel.doctor_added'));
    }

    public function editDoctor(Doctor $doctor): View
    {
        $this->authorize('update', $doctor);
        $doctor->load(['user', 'schedules']);

        return view('clinic.doctors.form', [
            'doctor' => $doctor,
            'clinic' => $this->clinic(),
        ]);
    }

    public function updateDoctor(Request $request, Doctor $doctor, DoctorService $doctors): RedirectResponse
    {
        $this->authorize('update', $doctor);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'digits:10', Rule::unique('users', 'mobile')->ignore($doctor->user_id)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($doctor->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'specialization' => ['required', 'string', 'max:120'],
            'qualification' => ['required', 'string', 'max:160'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'consultation_fee' => ['required', 'integer', 'min:0'],
            'room' => ['required', 'string', 'max:40'],
            'bio' => ['nullable', 'string', 'max:800'],
            'max_tokens_per_day' => ['required', 'integer', 'min:1', 'max:200'],
            'avg_consultation_minutes' => ['required', 'integer', 'min:5', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $rows = $this->scheduleRows($request);

        if ($rows === []) {
            return back()->withInput()->withErrors(['schedules' => __('ui.panel.schedule_required')]);
        }

        $doctors->update($doctor, $data, $rows);

        return redirect()->route('clinic.doctors')->with('status', __('ui.common.saved'));
    }

    public function storeLeave(Request $request, Doctor $doctor, DoctorService $doctors): RedirectResponse
    {
        $this->authorize('update', $doctor);
        $data = $request->validate([
            'leave_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:160'],
        ]);

        $doctors->grantLeave($doctor, $data['leave_date'], $data['reason'] ?? null);

        return back()->with('status', __('ui.doctor.leave_saved'));
    }

    public function destroyLeave(DoctorLeave $leave): RedirectResponse
    {
        $this->authorize('update', $leave->doctor);
        $leave->delete();

        return back()->with('status', __('ui.common.saved'));
    }

    public function reviews(): View
    {
        $clinic = $this->clinic();
        $reviews = Review::query()->with(['patient', 'doctor.user'])->where('clinic_id', $clinic->id)->latest()->paginate(12);

        return view('clinic.reviews', compact('clinic', 'reviews'));
    }

    public function respond(Request $request, Review $review, ReviewService $reviews): RedirectResponse
    {
        abort_unless($review->clinic_id === $this->clinic()->id, 403);
        $data = $request->validate(['response' => ['required', 'string', 'max:500']]);
        $reviews->respond($review, $data['response']);

        return back()->with('status', __('ui.panel.response_saved'));
    }

    public function reports(Request $request, AnalyticsService $analytics, ReportService $reports): View
    {
        $clinic = $this->clinic();
        $from = $request->string('from')->toString() ?: now()->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: now()->toDateString();

        return view('clinic.report', [
            'clinic' => $clinic,
            'stats' => $analytics->clinic($clinic, $from, $to),
            'rows' => $reports->rows($clinic, $from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function export(Request $request, ReportService $reports): StreamedResponse
    {
        $clinic = $this->clinic();
        $from = $request->string('from')->toString() ?: now()->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: now()->toDateString();

        return $reports->csv($clinic, $from, $to);
    }

    public function storeReceptionist(Request $request, ClinicService $clinics): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'digits:10', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $clinics->addReceptionist($this->clinic(), $data);

        return back()->with('status', __('ui.panel.reception_added'));
    }

    private function clinic(): Clinic
    {
        $clinic = auth()->user()->ownedClinic;

        abort_unless($clinic, 403);

        return $clinic;
    }

    /**
     * @return array<int, array{day_of_week: int, start_time: string, end_time: string}>
     */
    private function scheduleRows(Request $request): array
    {
        $rows = [];

        foreach (range(0, 6) as $day) {
            foreach (['start' => 'end', 'evening_start' => 'evening_end'] as $startKey => $endKey) {
                $start = $request->input("schedules.$day.$startKey");
                $end = $request->input("schedules.$day.$endKey");

                if ($start && $end) {
                    $rows[] = [
                        'day_of_week' => $day,
                        'start_time' => $start,
                        'end_time' => $end,
                    ];
                }
            }
        }

        return $rows;
    }
}
