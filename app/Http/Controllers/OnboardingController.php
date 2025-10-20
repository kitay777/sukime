<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Onboarding', [
            'user' => $request->user()->only([
                'name','email','real_name','school_name','grade','class_or_department'
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'real_name'            => ['required','string','max:255'],
            'school_name'          => ['required','string','max:255'],
            'grade'                => ['required','string','max:50'],   // 例: 1年 / 高1 / B2
            'class_or_department'  => ['required','string','max:255'],  // 例: 1組 / 経済学部 経済学科
        ]);

        $request->user()->forceFill($validated + ['profile_completed' => true])->save();

        return redirect()->intended(route('dashboard'));
    }
}