<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyAccountRequest;
use App\Http\Requests\Admin\LoginRequest;
use App\Services\Admin\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function index()
    {
        return $this->authService->redirectAdminEntry();
    }

    public function showLogin()
    {
        return $this->authService->showLoginPage();
    }

    public function login(LoginRequest $request)
    {
        $this->authService->login(
            $request->validated(),
            $request->boolean('remember')
        );

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function destroyAccount(DestroyAccountRequest $request)
    {
        return $this->authService->destroyAccount($request, $request->user());
    }
}
