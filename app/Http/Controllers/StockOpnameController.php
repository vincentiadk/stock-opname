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

    public function save(Request $request)
    {
        try{
            $datas = [
                [ "name" => "STOCKOPNAMEID", 'Value' => $request->input('stockopnameid') ],
                [ "name" => "LISTDATA", "Value" => implode(',' , $request->input('listdata')) ],
                [ 'name' => 'LOCATION_ID', 'Value' => $request->input('location_id')],
                [ "name" => 'LOCATION_SHELF_ID', 'Value' => $request->input('location_shelf_id')],
                [ 'name' => 'LOCATION_RUGS_ID', 'Value' => $request->input('location_rugs_id')],
                [ "name" => "JENIS", 'Value' => $request->input('jenis') ],
                [ "name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s') ],
                [ "name" => "CREATEBY", "Value"=> session('user')["username"]], 
                [ "name" => "CREATETERMINAL", "Value"=> \Request::ip()]
            ]; 
            $res =  Http::post($this->url ."?token=" . $this->token."&op=add&table=STOCKOPNAMEJOBS&issavehistory=0&ListAddItem=" . urlencode(json_encode($datas)));
            if($res["Status"] == "Success"){
                return response()->json([
                    "Message" => "Success",
                ], 200);
            } else {
                return response()->json([
                    "Message" => $res["Message"],
                ], 500);
            }
        } catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }

    public function synchronize()
    {
        
    }
}
