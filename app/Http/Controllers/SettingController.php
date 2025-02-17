<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
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
        $setting = Setting::where('user_id', session('user')["id"])->first();
        return view('setting', [
            'title' => 'Setting',
            'setting' => $setting,
        ]);
    }

    public function add($table)
    {

    }

    public function delete($table, $id)
    {

    }

    public function modify($table, $id)
    {
        
    }

    public function getLocation()
    {
        $sql = "SELECT ID,NAME FROM LOCATIONS where locationlibrary_id=1 ";
        $data = Http::post($this->url ."?token=$this->token&op=getlistraw&sql=$sql")["Data"]["Items"];

        $arr = [];
        foreach($data as $d){
            array_push($arr, [
                'id' => $d['NAME'],
                'nama' => $d['NAME'],
                'text'=>$d['NAME']
            ]);
        }
       
        return $arr;
    }

    public function saveLocation()
    {
        Setting::updateOrCreate([
            'user_id' => session('user')['id']
        ], [
            'location_id' => request('location_id')
        ]);
        return response()->json([
            "Message" => "Success"
        ], 200);
    }
    public function getLocationShelf($id)
    {
        $sql = "SELECT * FROM LOCATION_SHELF where location_id=$id ";
        $res = Http::post($this->url ."?token=$this->token&op=getlistraw&sql=$sql");
        return $res;
    }

    public function getLocationRugs($id)
    {
        $sql = "SELECT * FROM LOCATION_RUGS where location_shelf_id=$id ";
        $res = Http::post($this->url ."?token=$this->token&op=getlistraw&sql=$sql");
        return $res;
    }
}
