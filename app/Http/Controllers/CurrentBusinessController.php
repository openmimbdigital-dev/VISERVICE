<?php

namespace App\Http\Controllers;

use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrentBusinessController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'business_id' => ['required', 'integer', 'exists:businesses,id'],
        ]);

        CurrentBusiness::switchForUser(auth()->user(), (int) $request->input('business_id'));

        return redirect()->back();
    }
}
