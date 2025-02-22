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
        $location = $this->getLocation();  $locationShelf = []; $locationRugs = [];
        $stockopname = $this->getStockOpname();
        if($setting){
            if($setting->location_id){
                $locationShelf = $this->getLocationShelf($setting->location_id);
            }
            if($setting->location_shelf_id){
                $locationRugs = $this->getLocationRugs($setting->location_shelf_id);
            }
        } else {
            $setting = new Setting();
        }
        return view('setting', [
            'title' => 'Setting',
            'setting' => $setting,
            'locations' => $location,
            'location_rugs' => $locationRugs,
            'location_shelf' => $locationShelf,
            'stockopname' => $stockopname,

        ]);
    }

    public function getLocation()
    {
        $sql = "SELECT ID,NAME FROM LOCATIONS where locationlibrary_id=1 order by upper(name)";
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
            $setting = Setting::updateOrCreate([
                'user_id' => session('user')['id']
            ], [
                'location_id' => $request->input('location_id'),
                'location_name' => $request->input('location_name'),
                'location_shelf_id' => null,
                'location_shelf_name' => null
            ])->refresh();
            session(['setting' => $setting->toArray()]);
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
        $sql = "SELECT * FROM LOCATION_SHELF where location_id=$id order by upper(name)";
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
        try{
            $setting = Setting::updateOrCreate([
                'user_id' => session('user')['id']
            ], [
                'location_shelf_id' => $request->input('location_shelf_id'),
                'location_shelf_name' => $request->input('location_shelf_name'),
                'location_rugs_id' => $request->input('location_rugs_id'),
                'location_rugs_name' => $request->input('location_rugs_name')
            ])->refresh();
            session(['setting' => $setting->toArray()]);
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
        $sql = "SELECT ID, NAME FROM LOCATION_RUGS where location_shelf_id=$id order by upper(name) ";
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
            $setting = Setting::updateOrCreate([
                'user_id' => session('user')['id']
            ], [
                'location_rugs_id' => $request->input('location_rugs_id'),
                'location_rugs_name' => $request->input('location_rugs_name')
            ])->refresh();
            session(['setting' => $setting->toArray()]);
            return response()->json([
                "Message" => "Success"
            ], 200);
        } catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }

    public function getStockOpname()
    {
        $sql = "SELECT ID,PROJECTNAME FROM STOCKOPNAME ";
        $res = kurl("get","getlistraw", "", $sql, 'sql', '')["Data"]["Items"];
        $arr = [];
        foreach($res as $d){
            array_push($arr, [
                'id' => $d['ID'],
                'nama' => $d['PROJECTNAME'],
                'text'=>$d['PROJECTNAME']
            ]);
        }
       
        return $arr;
    }

    public function saveStockopname(Request $request)
    {
        try{
            $setting = Setting::updateOrCreate([
                'user_id' => session('user')['id']
            ], [
                'stockopname_id' => $request->input('stockopname_id'),
                'stockopname_name' => $request->input('stockopname_name'),
            ])->refresh();
            session(['setting' => $setting->toArray()]);
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
