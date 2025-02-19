<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class LocationController extends BaseController
{

    public function add(Request $request, $table)
    {
        try{
            switch($table){
                case 'location_shelf':
                    $datas = [
                        [ "name" => "LOCATION_ID", "Value" => $request->input('location_id') ],
                        [ "name" => "NAME", "Value" => $request->input('name') ],
                        [ "name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s') ],
                        [ "name" => "CREATEBY", "Value"=> session('user')["username"]], 
                        [ "name" => "CREATETERMINAL", "Value"=> \Request::ip()]
                    ]; 
                    $res =  Http::post($this->url ."?token=" . $this->token."&op=add&table=LOCATION_SHELF&issavehistory=0&ListAddItem=" . urlencode(json_encode($datas)));
                    \Log::info($res);
                    if($res["Status"] == "Success"){
                        Setting::updateOrCreate([
                            'user_id' => session('user')['id']
                        ], [
                            'location_shelf_id' => $res['Data']['ID'],
                            'location_shelf_name' => $request->input('name')
                        ]);
                        return response()->json([
                            "Message" => "Success"
                        ], 200);
                    } else {
                        return response()->json([
                            "Message" => $res["Message"],
                            "ID" => $res['Data']['ID'],
                        ], 500);
                    }
                    break;
                case 'location_rugs' : 
                    $datas = [
                        [ "name" => "LOCATION_SHELF_ID", "Value" => $request->input('location_shelf_id') ],
                        [ "name" => "NAME", "Value" => $request->input('name') ],
                        [ "name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s') ],
                        [ "name" => "CREATEBY", "Value"=> session('user')["username"]], 
                        [ "name" => "CREATETERMINAL", "Value"=> \Request::ip()]
                    ]; 
                    $res =  Http::post($this->url ."?token=" . $this->token."&op=add&table=LOCATION_RUGS&issavehistory=0&ListAddItem=" . urlencode(json_encode($datas)));
                    if($res["Status"] == "Success"){
                        Setting::updateOrCreate([
                            'user_id' => session('user')['id']
                        ], [
                            'location_rug_id' => $res['Data']['ID'],
                            'location_rug_name' => $request->input('name')
                        ]);
                        return response()->json([
                            "Message" => "Success",
                            "ID" => $res['Data']['ID'],
                        ], 200);
                    } else {
                        return response()->json([
                            "Message" => $res["Message"]
                        ], 500);
                    }
                    break;
                    
                    break;
                default: break;
            }
            
        } catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }

    public function delete($table, $id)
    {

    }

    public function modify(Request $request, $table, $id)
    {
        
    }

}
