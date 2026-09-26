<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicRegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PatientRegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\ClinicService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        if ($request->filled('redirect') && str_starts_with((string) $request->query('redirect'), url('/'))) {
            redirect()->setIntendedUrl((string) $request->query('redirect'));
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request, AuthService $auth): RedirectResponse
    {
        $user = $auth->attempt($request->string('mobile')->toString(), $request->string('password')->toString());

        if (! $user) {
            return back()->withInput($request->only('mobile'))->withErrors([
                'mobile' => __('ui.auth.failed'),
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(PatientRegisterRequest $request, AuthService $auth): RedirectResponse
    {
        try {
            $user = $auth->registerPatient($request->validated());
        } catch (OtpException $exception) {
            return back()->withInput()->withErrors(['mobile' => $exception->getMessage()]);
        }

        session([
            'otp_mobile' => $user->mobile,
            'otp_purpose' => 'register',
            'demo_otp' => app(OtpService::class)->demoCode($user->mobile),
        ]);

        return redirect()->route('otp.show');
    }

    public function showClinicRegister(): View
    {
        return view('auth.register-clinic', [
            'specialties' => config('arogya.specialties'),
        ]);
    }

    public function registerClinic(ClinicRegisterRequest $request, ClinicService $clinics, AuthService $auth): RedirectResponse
    {
        $data = $request->validated();
        $clinic = $clinics->register(
            [
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
            ],
            [
                'name' => $data['clinic_name'],
                'specialty' => $data['specialty'],
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'] ?? 'Gujarat',
                'pincode' => $data['pincode'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'description' => $data['description'] ?? null,
                'email' => $data['email'] ?? null,
            ],
            $request->file('document'),
        );

        $auth->login($clinic->admin);

        return redirect()->route('clinic.dashboard')->with('status', __('ui.clinic.pending_note'));
    }

    public function showOtp(): View
    {
        abort_unless(session('otp_mobile'), 403);

        return view('auth.otp', [
            'mobile' => session('otp_mobile'),
            'demoOtp' => session('demo_otp'),
        ]);
    }

    public function verifyOtp(Request $request, OtpService $otp, AuthService $auth): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $mobile = session('otp_mobile');
        $purpose = session('otp_purpose', 'register');
        abort_unless($mobile, 403);

        if (! $otp->verify($mobile, $data['code'], $purpose)) {
            return back()->withErrors(['code' => __('ui.auth.otp_invalid')]);
        }

        $user = User::query()->where('mobile', $mobile)->firstOrFail();
        $user->update([
            'is_active' => true,
            'mobile_verified_at' => $user->mobile_verified_at ?? now(),
        ]);

        session()->forget(['otp_mobile', 'otp_purpose', 'demo_otp']);
        $auth->login($user);

        return redirect()->intended(route('dashboard'));
    }

    public function resendOtp(OtpService $otp): RedirectResponse
    {
        $mobile = session('otp_mobile');
        $purpose = session('otp_purpose', 'register');
        abort_unless($mobile, 403);

        try {
            $otp->issue($mobile, $purpose);
        } catch (OtpException $exception) {
            return back()->withErrors(['code' => $exception->getMessage()]);
        }

        session(['demo_otp' => $otp->demoCode($mobile)]);

        return back()->with('status', __('ui.auth.otp_sent'));
    }

    public function sendLoginOtp(Request $request, OtpService $otp): RedirectResponse
    {
        $data = $request->validate(['mobile' => ['required', 'digits:10']]);
        $user = User::query()->where('mobile', $data['mobile'])->where('is_active', true)->first();

        if (! $user) {
            return back()->withErrors(['mobile' => __('ui.auth.failed')]);
        }

        try {
            $otp->issue($user->mobile, 'login');
        } catch (OtpException $exception) {
            return back()->withErrors(['mobile' => $exception->getMessage()]);
        }

        session([
            'otp_mobile' => $user->mobile,
            'otp_purpose' => 'login',
            'demo_otp' => $otp->demoCode($user->mobile),
        ]);

        return redirect()->route('otp.show');
    }

    public function showForgot(): View
    {
        return view('auth.forgot');
    }

    public function sendResetOtp(Request $request, OtpService $otp): RedirectResponse
    {
        $data = $request->validate(['mobile' => ['required', 'digits:10']]);
        $user = User::query()->where('mobile', $data['mobile'])->first();

        if (! $user) {
            return back()->withErrors(['mobile' => __('ui.auth.failed')]);
        }

        try {
            $otp->issue($user->mobile, 'reset');
        } catch (OtpException $exception) {
            return back()->withErrors(['mobile' => $exception->getMessage()]);
        }

        session([
            'reset_mobile' => $user->mobile,
            'demo_otp' => $otp->demoCode($user->mobile),
        ]);

        return back()->with('status', __('ui.auth.otp_sent'));
    }

    public function resetPassword(Request $request, OtpService $otp): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $mobile = session('reset_mobile');
        abort_unless($mobile, 403);

        if (! $otp->verify($mobile, $data['code'], 'reset')) {
            return back()->withErrors(['code' => __('ui.auth.otp_invalid')]);
        }

        $user = User::query()->where('mobile', $mobile)->firstOrFail();
        $user->update([
            'password' => $data['password'],
            'is_active' => true,
            'mobile_verified_at' => $user->mobile_verified_at ?? now(),
        ]);

        session()->forget(['reset_mobile', 'demo_otp']);

        return redirect()->route('login')->with('status', __('ui.auth.password_updated'));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
