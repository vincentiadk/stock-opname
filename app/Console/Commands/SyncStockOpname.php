<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncStockOpname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-stock-opname';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sql = "SELECT * FROM STOCKOPNAMEJOBS WHERE is_sync = 1 ";
        $res =  kurl("get","getlistraw", "",  $sql, 'sql', '')["Data"]["Items"];
        if(count($res) > 0) {
            foreach($res as $so){
                $jenis = $so['JENIS'];
                $list_data = explode(',',$so['LISTDATA']);
                if($jenis == 'RFID'){
                    foreach($list_data as $d) {
                        $detail =  kurl("get","getlistraw", "",  
                                            "SELECT c.id, c.location_id, c.location_rugs_id, c.location_shelf_id, c.rfid, c.status, rc.serial_number
                                            FROM RFID_COLLECTIONS rc JOIN COLLECTIONS c on c.rfid = rfid_no AND SERIAL_NUMBER='".$d."'", 'sql', '')["Data"];
                        if(isset($detail["Data"]["Items"][0])) {
                            $current = $detail["Data"]["Items"][0];
                            $collections = [
                                ["name" => "LOCATION_ID", "Value" => $so['LOCATION_ID']],
                                ["name" => "LOCATION_SHELF_ID", "Value" => $so['LOCATION_SHELF_ID']],
                                ["name" => "LOCATION_RUGS_ID", "Value" => $so['LOCATION_RUGS_ID']],
                                ["name" => "UPDATEDATE", "Value" => $so['CREATEDATE']],
                                ["name" => "UPDATEDATE", "Value" => $so['CREATEBY']],
                                ["name" => "UPDATEDATE", "Value" => $so['CREATETERMINAL']],
                            ];
                            $res =  Http::post($this->url ."?token=" . $this->token."&op=update&id=".$detail['ID']."&table=COLLECTIONS&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($collections)));
                            if($res["Status"] == "Success"){
                                $stockopname = [
                                    ["name" => "STOCKOPNAMEID", "Value" => $so['STOCKOPNAMEID']],
                                    ["name" => "COLLECTIONID", "Value" => $detail['LOCATION_ID']],
                                    ["name" => "PREVLOCATIONID", "Value" => $detail['LOCATION_ID']],
                                    ["name" => "PREV_LOCATION_SHELF_ID", "Value" => $detail['LOCATION_SHELF_ID']],
                                    ["name" => "PREV_LOCATION_RUGS_ID", "Value" => $detail['LOCATION_RUGS_ID']],
                                    ["name" => "CURRENTLOCATIONID", "Value" => $so['LOCATION_ID']],
                                    ["name" => "CURRENT_LOCATION_RUGS_ID", "Value" => $so['LOCATION_RUGS_ID']],
                                    ["name" => "CURRENT_LOCATION_SHELF_ID", "Value" => $so['LOCATION_SHELF_ID']],
                                    ["name" => "RFID_NO", "Value" => $detail['RFID']],
                                    ["name" => "RFID_SERIAL_NUMBER", "Value" => $d],
                                    ["name" => "CREATEDATE", "Value" => $so['CREATEDATE']],
                                    ["name" => "CREATEBY", "Value" => $so['CREATEBY']],
                                    ["name" => "CREATETERMINAL", "Value" => $so['CREATETERMINAL']],
                                ];
                                $res2 =  Http::post($this->url ."?token=" . $this->token."&op=add&table=STOCKOPNAMEDETAIL&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($collections)));
                                if($res2["Status"] == "Success"){
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
                                    "Message" => $res["Message"],
                                ], 500);
                            }
                        } else {
                            $stockopname = [
                                ["name" => "STOCKOPNAMEID", "Value" => $so['STOCKOPNAMEID']],
                                ["name" => "PREVLOCATIONID", "Value" => $detail['LOCATION_ID']],
                                ["name" => "PREV_LOCATION_SHELF_ID", "Value" => $detail['LOCATION_SHELF_ID']],
                                ["name" => "PREV_LOCATION_RUGS_ID", "Value" => $detail['LOCATION_RUGS_ID']],
                                ["name" => "PROBLEM", "Value" => 'not found'],
                                ["name" => "RFID_SERIAL_NUMBER", "Value" => $d],
                                ["name" => "CREATEDATE", "Value" => $so['CREATEDATE']],
                                ["name" => "CREATEBY", "Value" => $so['CREATEBY']],
                                ["name" => "CREATETERMINAL", "Value" => $so['CREATETERMINAL']],
                            ];
                            $res2 =  Http::post($this->url ."?token=" . $this->token."&op=add&table=STOCKOPNAMEDETAIL&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($collections)));
                            if($res2["Status"] == "Success"){
                                return response()->json([
                                    "Message" => "Success",
                                ], 200);
                            } else {
                                return response()->json([
                                    "Message" => $res["Message"],
                                ], 500);
                            }
                        }
                    }
                } 
                if($jenis == 'BARQR'){
                    foreach($list_data as $d) {
                        $detail =  kurl("get","getlistraw", "",  
                                            "SELECT *
                                            FROM COLLECTIONS WHERE NOMORBARCODE='".$d."'", 'sql', '')["Data"];
                        if(isset($detail["Data"]["Items"][0])) {
                            $current = $detail["Data"]["Items"][0];
                            $collections = [
                                ["name" => "LOCATION_ID", "Value" => $so['LOCATION_ID']],
                                ["name" => "LOCATION_SHELF_ID", "Value" => $so['LOCATION_SHELF_ID']],
                                ["name" => "LOCATION_RUGS_ID", "Value" => $so['LOCATION_RUGS_ID']],
                                ["name" => "UPDATEDATE", "Value" => $so['CREATEDATE']],
                                ["name" => "UPDATEDATE", "Value" => $so['CREATEBY']],
                                ["name" => "UPDATEDATE", "Value" => $so['CREATETERMINAL']],
                            ];
                            $res =  Http::post($this->url ."?token=" . $this->token."&op=update&id=".$detail['ID']."&table=COLLECTIONS&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($collections)));
                            if($res["Status"] == "Success"){
                                $stockopname = [
                                    ["name" => "STOCKOPNAMEID", "Value" => $so['STOCKOPNAMEID']],
                                    ["name" => "COLLECTIONID", "Value" => $detail['LOCATION_ID']],
                                    ["name" => "PREVLOCATIONID", "Value" => $detail['LOCATION_ID']],
                                    ["name" => "PREV_LOCATION_SHELF_ID", "Value" => $detail['LOCATION_SHELF_ID']],
                                    ["name" => "PREV_LOCATION_RUGS_ID", "Value" => $detail['LOCATION_RUGS_ID']],
                                    ["name" => "CURRENTLOCATIONID", "Value" => $so['LOCATION_ID']],
                                    ["name" => "CURRENT_LOCATION_RUGS_ID", "Value" => $so['LOCATION_RUGS_ID']],
                                    ["name" => "CURRENT_LOCATION_SHELF_ID", "Value" => $so['LOCATION_SHELF_ID']],
                                    ["name" => "NOMORBARCODE", "Value" => $detail['NOMORBARCODE']],
                                    ["name" => "CREATEDATE", "Value" => $so['CREATEDATE']],
                                    ["name" => "CREATEBY", "Value" => $so['CREATEBY']],
                                    ["name" => "CREATETERMINAL", "Value" => $so['CREATETERMINAL']],
                                ];
                                $res2 =  Http::post($this->url ."?token=" . $this->token."&op=add&table=STOCKOPNAMEDETAIL&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($collections)));
                                if($res2["Status"] == "Success"){
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
                                    "Message" => $res["Message"],
                                ], 500);
                            }
                        } else {
                            $stockopname = [
                                ["name" => "STOCKOPNAMEID", "Value" => $so['STOCKOPNAMEID']],
                                ["name" => "PREVLOCATIONID", "Value" => $detail['LOCATION_ID']],
                                ["name" => "PREV_LOCATION_SHELF_ID", "Value" => $detail['LOCATION_SHELF_ID']],
                                ["name" => "PREV_LOCATION_RUGS_ID", "Value" => $detail['LOCATION_RUGS_ID']],
                                ["name" => "PROBLEM", "Value" => 'not found'],
                                ["name" => "NOMORBARCODE", "Value" => $d],
                                ["name" => "CREATEDATE", "Value" => $so['CREATEDATE']],
                                ["name" => "CREATEBY", "Value" => $so['CREATEBY']],
                                ["name" => "CREATETERMINAL", "Value" => $so['CREATETERMINAL']],
                            ];
                            $res2 =  Http::post($this->url ."?token=" . $this->token."&op=add&table=STOCKOPNAMEDETAIL&issavehistory=0&ListUpdateItem=" . urlencode(json_encode($collections)));
                            if($res2["Status"] == "Success"){
                                return response()->json([
                                    "Message" => "Success",
                                ], 200);
                            } else {
                                return response()->json([
                                    "Message" => $res["Message"],
                                ], 500);
                            }
                        }
                    }
                } 
            }
        }
    }
}
