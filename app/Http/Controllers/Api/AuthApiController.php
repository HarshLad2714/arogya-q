<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\OtpException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthApiController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Body: {mobile, password}
     * Response: {token, user}
     */
    public function login(Request $request, AuthService $auth): JsonResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'digits:10'],
            'password' => ['required', 'string'],
        ]);

        $user = $auth->verifyPassword($data['mobile'], $data['password']);

        if (! $user) {
            return response()->json(['message' => __('ui.auth.failed')], 422);
        }

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $this->profile($user),
        ]);
    }

    /**
     * POST /api/v1/auth/otp  {mobile}
     */
    public function otp(Request $request, OtpService $otp): JsonResponse
    {
        $data = $request->validate(['mobile' => ['required', 'digits:10']]);
        $user = User::query()->where('mobile', $data['mobile'])->where('is_active', true)->first();

        if (! $user) {
            return response()->json(['message' => __('ui.auth.failed')], 422);
        }

        try {
            $otp->issue($user->mobile, 'login');
        } catch (OtpException $exception) {
            return response()->json(['message' => $exception->getMessage()], 429);
        }

        return response()->json([
            'sent' => true,
            'demo_code' => config('arogya.otp.demo') ? $otp->demoCode($user->mobile) : null,
        ]);
    }

    /**
     * POST /api/v1/auth/verify {mobile, code}
     */
    public function verify(Request $request, OtpService $otp): JsonResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'digits:10'],
            'code' => ['required', 'digits:6'],
        ]);

        if (! $otp->verify($data['mobile'], $data['code'], 'login')) {
            return response()->json(['message' => __('ui.auth.otp_invalid')], 422);
        }

        $user = User::query()->where('mobile', $data['mobile'])->firstOrFail();

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $this->profile($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->profile($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    private function profile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'role' => $user->role->value,
            'language' => $user->language_pref,
        ];
    }
}
