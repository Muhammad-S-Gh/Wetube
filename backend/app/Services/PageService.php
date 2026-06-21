<?php

namespace App\Services;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PageService
{
    public function home(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()?->is_admin
                ? redirect()->route('admin.dashboard')
                : redirect()->route('dashboard');
        }

        return view('pages.welcome');
    }
}
