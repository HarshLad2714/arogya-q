<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $clinics = Clinic::query()
            ->approved()
            ->withCount('doctors')
            ->withAvg('reviews', 'rating')
            ->latest('approved_at')
            ->limit(3)
            ->get();

        return view('home', [
            'clinics' => $clinics,
            'specialties' => config('arogya.specialties'),
            'board' => $clinics->first(),
        ]);
    }
}
