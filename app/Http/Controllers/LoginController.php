<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
class LoginController extends BaseController
{
    public function index()
    {
        if (session('user') == null) {
            return view('login', [
                'title' => 'Login'
            ]);
        } else {
            return redirect('/home');
        }
    }
    public function submit()
    {
        $username = request('username');
        $user = Http::post($this->url . "?token=" . $this->token . "&op=isloginvalid&UserName=" . $username . '&UserPassword=' . request('password'));
        if($user["Status"] == "Success"){
            $id = $user["Data"]["Id"];
            session([
                'user' => [
                    'id' => $id,
                    'username' => $username,
                ]]);

            $setting = Setting::updateOrCreate(['user_id'=>$id])->refresh();

            session(['setting' => $setting->toArray()]);
            return redirect('/home');
        } else{
            return redirect('/login')->with('error', $user["Message"]);
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}
