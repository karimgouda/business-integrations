<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('app.auth.login');
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            $request->session()->flash('toast', [
                'type' => 'success',
                'message' => 'Login Successfully Welcome Back'
            ]);
            return redirect()->intended();
        }

        $request->session()->flash('toast', [
            'type' => 'error',
            'message' => 'These credentials do not match our records.'
        ]);

        return back();
    }

    public function logout()
    {
        auth()->logout();
        return back();
    }
}
