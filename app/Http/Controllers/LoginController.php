<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    protected $url;
    protected $token;

    public function __construct() 
    {
        $this->url = config('inlis.url');
        $this->token = config('inlis.token');
    }
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
        $username = request('username');
        $user = Http::post($this->url . "?token=" . $this->token . "&op=isloginvalid&UserName=" . $username . '&UserPassword=' . request('password'));
        if($user["Status"] == "Success"){
            $id = $user["Data"]["Id"];
            session([
                'user' => [
                    'id' => $id,
                    'username' => $username
                ]]);
            return redirect('/tagging');
        } else{
            return response()->json([
                "Status" => $user["Status"],
                "Message" => $user["Message"]
            ], 500);
        }
    }
}
