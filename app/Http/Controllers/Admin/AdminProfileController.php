<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    /**
     * Display the admin profile page.
     */
    public function edit(Request $request): View
    {
        return view('Panel-admin.perfil', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the admin profile information (name, email).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        $shouldLogout = false;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $shouldLogout = true;
        }

        if ($user->isDirty('name')) {
            $shouldLogout = true;
        }

        if ($shouldLogout) {
            $user->force_logout_at = now();
        }

        $user->save();

        if ($shouldLogout) {
            return $this->logoutAndRedirect($request, __('Tu información fue actualizada. Inicia sesión nuevamente.'));
        }

        return redirect()->route('admin.perfil')->with('status', 'admin-profile-updated');
    }

    /**
     * Update the admin password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = $validated['password'];
        $user->force_logout_at = now();
        $user->save();

        return $this->logoutAndRedirect($request, __('Tu contraseña fue actualizada. Inicia sesión con los nuevos datos.'));
    }

    private function logoutAndRedirect(Request $request, string $message): RedirectResponse
    {
        $user = $request->user();

        if ($user && ! $user->force_logout_at) {
            $user->force_logout_at = now();
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', $message);
    }
}
