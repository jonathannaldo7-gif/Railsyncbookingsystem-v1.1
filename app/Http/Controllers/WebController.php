<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
    }

    public function users()
    {
        return view('users');
    }

    public function login()
    {
        return view('login');
    }

    public function trains()
    {
        return view('trains');
    }

    public function seats()
    {
        return view('seats');
    }

    public function bookings()
    {
        return view('bookings');
    }
}
