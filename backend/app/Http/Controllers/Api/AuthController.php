<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public static function profile(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'role', 'cv_name']);
    }

    public function csrf(Request $request)
    {
        return response()->json(['token' => $request->session()->token()])->header('Cache-Control', 'no-store');
    }

    public function register(Request $request)
    {
        abort_if(Auth::check(), 409, 'Sign out before creating another account.');
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|max:254|unique:users,email',
            'password' => ['required', 'confirmed', 'max:72', Password::min(12)->mixedCase()->numbers()],
            'role' => 'required|in:candidate,employer',
            'company_name' => 'required_if:role,employer|nullable|string|max:120',
        ]);
        $user = DB::transaction(function () use ($data) {
            $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $user->role = $data['role'];
            $user->save();
            if ($user->role === 'employer') {
                $company = new Company([
                    'name' => $data['company_name'], 'slug' => Str::slug($data['company_name']).'-'.Str::lower(Str::random(8)),
                    'logo_text' => Str::upper(Str::substr($data['company_name'], 0, 2)),
                ]);
                $company->owner_id = $user->id;
                $company->save();
            }
            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();
        return response()->json(['user' => self::profile($user)], 201);
    }

    public function login(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate(['email' => 'required|email|max:254', 'password' => 'required|string|max:72']);
        if (!Auth::attempt([...$data, 'is_active' => true])) {
            throw ValidationException::withMessages(['email' => 'The credentials are incorrect or the account is unavailable.']);
        }
        $request->session()->regenerate();
        return response()->json(['user' => self::profile($request->user())]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => self::profile($request->user())])->header('Cache-Control', 'no-store');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Signed out.']);
    }
}
