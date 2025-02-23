<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class StockOpnameController extends BaseController
{
    public function index()
    {
        $setting = Setting::updateOrCreate(['user_id' => session('user')['id']])->refresh();
        return view('stockopname', [
            'title'   => 'Stock Opname',
            'setting' => $setting,
        ]);
    }

    public function save()
    {}

    public function synchronize()
    {}
}
