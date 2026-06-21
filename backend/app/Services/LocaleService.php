<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;

class LocaleService
{
    public function switch(string $locale): RedirectResponse
    {
        session(['locale' => $locale]);

        return redirect()->back();
    }
}
