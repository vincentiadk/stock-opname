<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public $url ;
    public $token ;

    public function __construct()
    {
        $this->url =  config('inlis.url');
        $this->token =  config('inlis.token');
    }
}
