<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Services\PageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WelcomeController extends Controller
{
    public function __construct(
        protected PageService $pageService,
    ) {}

    public function __invoke(): View|RedirectResponse
    {
        return $this->pageService->home();
    }
}
