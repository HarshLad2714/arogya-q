<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route(match (auth()->user()->role) {
            UserRole::SuperAdmin => 'platform.dashboard',
            UserRole::ClinicAdmin => 'clinic.dashboard',
            UserRole::Doctor => 'doctor.dashboard',
            UserRole::Receptionist => 'desk.dashboard',
            default => 'patient.dashboard',
        });
    }
}
