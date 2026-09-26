<?php

namespace App\Http\Controllers;

use App\Enums\ClinicStatus;
use App\Models\Clinic;
use Illuminate\View\View;

class DisplayController extends Controller
{
    public function show(Clinic $clinic): View
    {
        abort_unless($clinic->status === ClinicStatus::Approved, 404);

        return view('display.show', [
            'clinic' => $clinic,
        ]);
    }
}
