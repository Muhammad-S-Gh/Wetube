<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locale\SwitchLocaleRequest;
use App\Services\LocaleService;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __construct(
        protected LocaleService $localeService,
    ) {}

    public function switch(SwitchLocaleRequest $request): RedirectResponse
    {
        return $this->localeService->switch($request->validated()['locale']);
    }
}
