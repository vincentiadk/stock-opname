<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        if (session('user') == null) {
            return view('login', [
                'title' => 'Login'
            ]);
        } else {
            return redirect('/tagging');
        }
    }
    public function submit()
    {
        //do something
    }
}
