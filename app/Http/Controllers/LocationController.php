<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class LocationController extends Controller
{
    protected $url ;
    protected $token ;

    public function __construct()
    {
        $this->url =  config('inlis.url');
        $this->token =  config('inlis.token');
    }
    public function add(Request $request, $table)
    {
        try{
            switch($table){
                case 'location_shelf':
                    if($this->checkShelf($request->input('location_id'), null, $request->input('name')) == 0) {
                        $datas = [
                            [ "name" => "LOCATION_ID", "Value" => $request->input('location_id') ],
                            [ "name" => "NAME", "Value" => $request->input('name') ],
                            [ "name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s') ],
                            [ "name" => "CREATEBY", "Value"=> session('user')["username"]], 
                            [ "name" => "CREATETERMINAL", "Value"=> \Request::ip()]
                        ]; 
                        $res =  Http::post($this->url ."?token=" . $this->token."&op=add&table=LOCATION_SHELF&issavehistory=0&ListAddItem=" . urlencode(json_encode($datas)));
                        if($res["Status"] == "Success"){
                            Setting::updateOrCreate([
                                'user_id' => session('user')['id']
                            ], [
                                'location_shelf_id' => $res['Data']['ID'],
                                'location_shelf_name' => $request->input('name')
                            ]);
                            return response()->json([
                                "Message" => "Success",
                                "ID" => $res['Data']['ID'],
                            ], 200);
                        } else {
                            return response()->json([
                                "Message" => $res["Message"],
                                
                            ], 500);
                        }
                    } else {
                        return response()->json([
                            "Message" => "Nama rak sudah ada!",
                        ], 500);
                    }
                    break;
                case 'location_rugs' : 
                    if($this->checkRugs($request->input('location_id'),$request->input('location_shelf_id') ,null, $request->input('name')) == 0) {
                        $datas = [
                            [ "name" => "LOCATION_ID", "Value" => $request->input('location_id') ],
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
                    } else {
                        return response()->json([
                            "Message" => "Nama ambal sudah ada!",
                        ], 500);
                    }
                    
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
        try{
            switch($table){
                case 'location_shelf' : 
                    $res =  Http::post($this->url ."?token=" . $this->token."&id=$id&op=delete&table=LOCATION_SHELF&deleteby=" . session('user')['username'] . "&terminal=" . \Request::ip());
                    if($res["Status"] == "Success"){
                        Setting::updateOrCreate([
                            'user_id' => session('user')['id']
                        ], [
                            'location_shelf_id' => null,
                            'location_shelf_name' => null,
                            'location_rugs_id' => null,
                            'location_rugs_name' => null
                        ]);
                        return response()->json([
                            "Message" => "Success",
                        ], 200);
                    } else {
                        return response()->json([
                            "Message" => $res["Message"],
                        ], 500);
                    }
                    break;
                case 'location_rugs' : 
                    $res =  Http::post($this->url ."?token=" . $this->token."&id=$id&op=delete&table=LOCATION_RUGS&deleteby=" . session('user')['username'] . "&terminal=" . \Request::ip());
                    if($res["Status"] == "Success"){
                        Setting::updateOrCreate([
                            'user_id' => session('user')['id']
                        ], [
                            'location_rugs_id' => null,
                            'location_rugs_name' => null
                        ]);
                        return response()->json([
                            "Message" => "Success",
                        ], 200);
                    } else {
                        return response()->json([
                            "Message" => $res["Message"], 
                        ], 500);
                    }
                    break;
                default:break;
            }
            
        }catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }

    public function modify(Request $request, $table, $id)
    {
        try{
            switch($table){
                case 'location_shelf':
                    if(intval($this->checkShelf($request->input('location_id'), $id, $request->input('name'))) == 0) {
                        $datas = [
                            [ "name" => "NAME", "Value" => $request->input('name') ],
                            [ "name" => "UPDATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s') ],
                            [ "name" => "UPDATEBY", "Value"=> session('user')["username"]], 
                            [ "name" => "UPDATETERMINAL", "Value"=> \Request::ip()]
                        ]; 
                        $res =  Http::post($this->url ."?token=" . $this->token."&id=$id&op=update&table=LOCATION_SHELF&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($datas)));
                        if($res["Status"] == "Success"){
                            Setting::updateOrCreate([
                                'user_id' => session('user')['id']
                            ], [
                                'location_shelf_id' => $res['Data']['ID'],
                                'location_shelf_name' => $request->input('name')
                            ]);
                            return response()->json([
                                "Message" => "Success",
                            ], 200);
                        } else {
                            return response()->json([
                                "Message" => $res["Message"],
                                
                            ], 500);
                        }
                    } else {
                        return response()->json([
                            "Message" => "Nama rak sudah ada!",
                        ], 500);
                    }
                    break;
                case 'location_rugs' : 
                    if(intval($this->checkRugs($request->input('location_id'),$request->input('location_shelf_id') , $id, $request->input('name'))) == 0) {
                        $datas = [
                            [ "name" => "NAME", "Value" => $request->input('name') ],
                            [ "name" => "UPDATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s') ],
                            [ "name" => "UPDATEBY", "Value"=> session('user')["username"]], 
                            [ "name" => "UPDATETERMINAL", "Value"=> \Request::ip()]
                        ]; 
                        $res =  Http::post($this->url ."?token=" . $this->token."&id=$id&op=update&table=LOCATION_RUGS&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($datas)));
                        if($res["Status"] == "Success"){
                            Setting::updateOrCreate([
                                'user_id' => session('user')['id']
                            ], [
                                'location_rug_id' => $res['Data']['ID'],
                                'location_rug_name' => $request->input('name')
                            ]);
                            return response()->json([
                                "Message" => "Success",
                            ], 200);
                        } else {
                            return response()->json([
                                "Message" => $res["Message"]
                            ], 500);
                        }
                    } else {
                        return response()->json([
                            "Message" => "Nama ambal sudah ada!",
                        ], 500);
                    }
                    break;
                default: break;
            }
            
        } catch(\Exception $e ) {
            return response()->json([
                "Message" => $e->getMessage()
            ], 500);
        }
    }

    function checkShelf($location_id, $location_shelf_id, $name)
    {
        if($location_shelf_id != null){
            $sql = "SELECT count(*) JML FROM location_shelf where upper(name) = upper('$name') AND id != $location_shelf_id AND location_id = $location_id ";
        } else {
            $sql = "SELECT count(*) JML FROM location_shelf where upper(name) = upper('$name') AND location_id = $location_id ";
        }
        $res = kurl("get","getlistraw", "", $sql, 'sql', '')["Data"]["Items"][0]["JML"];
        return $res;
    }

    function checkRugs($location_id, $location_shelf_id, $location_rugs_id, $name)
    {
        if($location_rugs_id !=null){
            $sql = "SELECT count(*) JML FROM location_rugs where upper(name) = upper('$name') AND id != $location_rugs_id AND location_shelf_id = $location_shelf_id AND location_id = $location_id";
        } else {
            $sql = "SELECT count(*) JML FROM location_rugs where upper(name) = upper('$name') AND location_shelf_id = $location_shelf_id AND location_id = $location_id ";
        }
        $res = kurl("get","getlistraw", "", $sql, 'sql', '')["Data"]["Items"][0]["JML"];
        return $res;
    }
}
