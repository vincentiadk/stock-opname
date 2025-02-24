<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends BaseController
{
    public function index()
    {
        $setting = Setting::updateOrCreate(['user_id' => session('user')['id']])->refresh();
        return view('home', [
            'title'   => 'Home',
            'setting' => $setting,
        ]);
    }
}
