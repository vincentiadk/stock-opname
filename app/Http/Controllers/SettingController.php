<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function index()
    { 
        $setting = Setting::where('user_id', session('user')["id"])->first();
        return view('setting', [
            'title' => 'Setting',
            'setting' => $setting,
        ]);
    }

    public function getLocation()
    {
        $sql = "SELECT ID,NAME FROM LOCATIONS where locationlibrary_id=1 ";
        $res = kurl("get","getlistraw", "", $sql, 'sql', '')["Data"]["Items"];
        $arr = [];
        foreach($res as $d){
            array_push($arr, [
                'id' => $d['ID'],
                'nama' => $d['NAME'],
                'text'=>$d['NAME']
            ]);
        }
       
        return $arr;
    }

    public function saveLocation(Request $request)
    {
        try{
            Setting::updateOrCreate([
                'user_id' => session('user')['id']
            ], [
                'location_id' => $request->input('location_id'),
                'location_name' => $request->input('location_name')
            ]);
            return response()->json([
                "Message" => "Success"
            ], 200);
        } catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }

    public function getLocationShelf($id)
    {
        $sql = "SELECT * FROM LOCATION_SHELF where location_id=$id ";
        $res = kurl("get","getlistraw", "", $sql, 'sql', '')["Data"]["Items"];
        $arr = [];
        foreach($res as $d){
            array_push($arr, [
                'id' => $d['ID'],
                'nama' => $d['NAME'],
                'text'=>$d['NAME']
            ]);
        }
       
        return $arr;
    }

    public function saveLocationShelf(Request $request)
    {
        \Log::info($request->all());
        try{
            Setting::updateOrCreate([
                'user_id' => session('user')['id']
            ], [
                'location_shelf_id' => $request->input('location_shelf_id'),
                'location_shelf_name' => $request->input('location_shelf_name')
            ]);
            return response()->json([
                "Message" => "Success"
            ], 200);
        } catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }

    public function getLocationRugs($id)
    {
        $sql = "SELECT ID, NAME FROM LOCATION_RUGS where location_shelf_id=$id ";
        $res = kurl("get","getlistraw", "", $sql, 'sql', '')["Data"]["Items"];
        $arr = [];
        foreach($res as $d){
            array_push($arr, [
                'id' => $d['ID'],
                'nama' => $d['NAME'],
                'text'=>$d['NAME']
            ]);
        }
       
        return $arr;
    }

    public function saveLocationRugs(Request $request)
    {
        try{
            Setting::updateOrCreate([
                'user_id' => session('user')['id']
            ], [
                'location_rugs_id' => $request->input('location_rugs_id'),
                'location_rugs_name' => $request->input('location_rugs_name')
            ]);
            return response()->json([
                "Message" => "Success"
            ], 200);
        } catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }
}
