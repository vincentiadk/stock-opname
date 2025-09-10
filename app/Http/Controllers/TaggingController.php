<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TaggingController extends BaseController
{
    public function searchItem(Request $request)
    {
        try {
            if($request->input('type') == 'barcode') {
                $sql = "SELECT Title, noinduk, noinduk_deposit, noinduk, collections.nomorbarcode, locations.name as location_name,
                        location_rugs.name as location_rugs_name, location_shelf.name as location_shelf_name,
                        collections.location_id, collections.location_shelf_id, collections.location_rugs_id, collections.id as colid, s.MASALAH
                        FROM COLLECTIONS
                        left join locations on locations.id = collections.location_id
                        left join location_shelf on location_shelf.id = collections.location_shelf_id
                        left join location_rugs on location_rugs.id = collections.location_rugs_id  
                        left join stockopnamedetail s on collections.id = s.collectionid WHERE collections.nomorbarcode = '" . trim($request->input('value')) . "'";
            } else {
                $sql = "SELECT rc.serial_number, c.rfid, c.Title, c.noinduk, c.noinduk_deposit, locations.name as location_name,
                    location_rugs.name as location_rugs_name, location_shelf.name as location_shelf_name,
                    c.location_id, c.location_shelf_id, c.location_rugs_id, c.id as colid, s.MASALAH
                    FROM RFID_COLLECTIONS rc
                    left join collections c on c.rfid = rc.rfid_no
                    left join locations on locations.id = c.location_id
                    left join location_shelf on location_shelf.id = c.location_shelf_id
                    left join location_rugs on location_rugs.id = c.location_rugs_id  
                    left join stockopnamedetail s on c.id = s.collectionid WHERE
                    rc.serial_number = '" . trim($request->input('value')) . "'";
            }

            $res = kurl("get", "getlistraw", "", $sql, 'sql', '');
            if ($res["Status"] == "Success") {
                if (isset($res["Data"]["Items"][0])) {
                    return response()->json(
                        [
                            "Status"  => "Success",
                            "Data"    => $res["Data"]["Items"][0],
                            "Message" => $res["Message"],
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            "Status"  => "Error",
                            "Message" => strtoupper($request->input('type')) .': '. $request->input('value') . " tidak ditemukan",
                        ]
                    );
                }

            } else {
                return response()->json([
                    "Status"  => "Error",
                    "Message" => strtoupper($request->input('type')) .': '. $request->input('value') . " tidak ditemukan",
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed Search Collection.',
                'err'     => $e->getMessage(),
                'status'  => 'Failed',
            ], 500);
        }
    }

    public function save()
    {
        try {
            $setting           = Setting::where('user_id', session('user')['id'])->first();
            $stockopnamebefore = [
                ["name" => "STOCKOPNAMEID", "Value" => $setting->stockopname_id],
                ["name" => "COLLECTIONID", "Value" => request('id')],
                ["name" => "NOMORBARCODE", "Value" => request('value')],   
            ];
            $so_masalah = array_merge($stockopnamebefore, [
                ["name" => "MASALAH", "Value" => "metadata"],
            ]);
            $collections = [
                ["name" => "LOCATION_ID", "Value" => $setting->location_id],
                ["name" => "LOCATION_SHELF_ID", "Value" => $setting->location_shelf_id],
                ["name" => "LOCATION_RUGS_ID", "Value" => $setting->location_rugs_id],
                ["name" => "UPDATEBY", "Value" => session('user')['username']],
                ["name" => "UPDATETERMINAL", "Value" => \Request::ip()],
                ["name" => "UPDATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
            ];
            $stockopnamedetail = array_merge($stockopnamebefore, [
                ["name" => "MASALAH", "Value" => ''],
                ["name" => "PREVLOCATIONID", "Value" => request('location_id')],
                ["name" => "PREV_LOCATION_SHELF_ID", "Value" => request('location_shelf_id')],
                ["name" => "PREV_LOCATION_RUGS_ID", "Value" => request('location_rugs_id')],
                ["name" => "CURRENTLOCATIONID", "Value" => $setting->location_id],
                ["name" => "CURRENT_LOCATION_SHELF_ID", "Value" => $setting->location_shelf_id],
                ["name" => "CURRENT_LOCATION_RUGS_ID", "Value" => $setting->location_rugs_id],
                ["name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
                ["name" => "CREATEBY", "Value" => session('user')["username"]],
                ["name" => "CREATETERMINAL", "Value" => \Request::ip()],
            ]);
            $id_exists = $this->checkStockOpnameExists($stockopnamebefore);
            // LOGIKA INI MENIMPA DATA SEBELUMNYA //
            if ($id_exists > 0){
                $res_col   = Http::post($this->url . "?token=" . $this->token . "&id=" . request('id') . "&op=update&table=COLLECTiONS&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($collections)));
                $res_stock = Http::post($this->url . "?token=" . $this->token . "&id=$id_exists&op=update&table=STOCKOPNAMEDETAIL&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($stockopnamedetail)));
                if ($res_col["Status"] == "Success") {
                    if ($res_stock["Status"] == "Success") {
                        return response()->json([
                            "Message" => "Success",
                        ], 200);
                    } else {
                        return response()->json([
                            "Message" => $res_stock["Message"],
                        ], 500);
                    }
                } else {
                    return response()->json([
                        "Message" => $res_col["Message"],
                    ], 500);
                }
            } else {
                $res_col   = Http::post($this->url . "?token=" . $this->token . "&id=" . request('id') . "&op=update&table=COLLECTiONS&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($collections)));
                $res_stock = Http::post($this->url . "?token=" . $this->token . "&op=add&table=STOCKOPNAMEDETAIL&issavehistory=1&ListAddItem=" . urlencode(json_encode($stockopnamedetail)));
                
                if ($res_col["Status"] == "Success") {
                    if ($res_stock["Status"] == "Success") {
                        return response()->json([
                            "Message" => "Success",
                        ], 200);
                    } else {
                        return response()->json([
                            "Message" => $res_stock["Message"],
                        ], 500);
                    }
                } else {
                    return response()->json([
                        "Message" => $res_col["Message"],
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'Message' => 'Failed Save Tagging: '. $e->getMessage(),
            ], 500);
        }
    }

    public function saveMasalah()
    {
        try {
            $setting           = Setting::where('user_id', session('user')['id'])->first();
            $collections = [
                ["name" => "LOCATION_ID", "Value" => ''],
                ["name" => "LOCATION_SHELF_ID", "Value" => ''],
                ["name" => "LOCATION_RUGS_ID", "Value" => ''],
                ["name" => "UPDATEBY", "Value" => session('user')['username']],
                ["name" => "UPDATETERMINAL", "Value" => \Request::ip()],
                ["name" => "UPDATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
            ];
            $stockopnamebefore = [
                ["name" => "STOCKOPNAMEID", "Value" => $setting->stockopname_id],
                ["name" => "COLLECTIONID", "Value" => request('id')],
                ["name" => "NOMORBARCODE", "Value" => request('value')],
            ];

            $so_masalah = array_merge($stockopnamebefore, [
                ["name" => "MASALAH", "Value" => "metadata"],
            ]);
            $stockopnamebefore = array_merge($stockopnamebefore, [
                ["name" => "MASALAH", "Value" => ''],
            ]);

            $id_masalah = $this->checkStockOpnameExists($so_masalah);
            $id_before = $this->checkStockOpnameExists($stockopnamebefore);
            if ($id_masalah > 0) { //kalau pernah ditagging bermasalah
                return response()->json([
                    "Message" => "Sudah pernah Anda tandai metadata bermasalah!",
                ], 500);
            }
            if ($id_before > 0) { // kalau pernah ditagging
                $so_masalah = array_merge($so_masalah, [
                    ["name" => "PREVLOCATIONID", "Value" => request('location_id')],
                    ["name" => "PREV_LOCATION_SHELF_ID", "Value" => request('location_shelf_id')],
                    ["name" => "PREV_LOCATION_RUGS_ID", "Value" => request('location_rugs_id')],
                    ["name" => "CURRENTLOCATIONID", "Value" => ''],
                    ["name" => "CURRENT_LOCATION_SHELF_ID", "Value" => ''],
                    ["name" => "CURRENT_LOCATION_RUGS_ID", "Value" => ''],
                    ["name" => "UPDATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
                    ["name" => "UPDATEBY", "Value" => session('user')["username"]],
                    ["name" => "UPDATETERMINAL", "Value" => \Request::ip()],
                ]);
                $res_col   = Http::post($this->url . "?token=" . $this->token . "&id=" . request('id') . "&op=update&table=COLLECTIONS&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($collections)));
                $res_stock = Http::post($this->url . "?token=" . $this->token . "&id=$id_before&op=update&table=STOCKOPNAMEDETAIL&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($so_masalah)));
                if($res_col["Status"] == "Success") {
                    if ($res_stock["Status"] == "Success") {
                        return response()->json([
                            "Message" => "Success",
                        ], 200);
                    } else {
                        return response()->json([
                            "Message" => $res_stock["Message"],
                        ], 500);
                    }
                } else {
                    return response()->json([
                        "Message" => $res_col["Message"],
                    ], 500);
                }
            } else {
                $so_masalah = array_merge($so_masalah, [
                    ["name" => "PREVLOCATIONID", "Value" => request('location_id')],
                    ["name" => "PREV_LOCATION_SHELF_ID", "Value" => request('location_shelf_id')],
                    ["name" => "PREV_LOCATION_RUGS_ID", "Value" => request('location_rugs_id')],
                    ["name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
                    ["name" => "CREATEBY", "Value" => session('user')["username"]],
                    ["name" => "CREATETERMINAL", "Value" => \Request::ip()],
                ]);
                $res_col   = Http::post($this->url . "?token=" . $this->token . "&id=" . request('id') . "&op=update&table=COLLECTIONS&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($collections)));
                $res_stock = Http::post($this->url . "?token=" . $this->token . "&op=add&table=STOCKOPNAMEDETAIL&issavehistory=1&ListAddItem=" . urlencode(json_encode($so_masalah)));
                if ($res_stock["Status"] == "Success") {
                    return response()->json([
                        "Message" => "Success",
                    ], 200);
                } else {
                    return response()->json([
                        "Message" => $res_stock["Message"],
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'Message' => 'Failed Save Tagging: '. $e->getMessage(),
            ], 500);
        }
    }

    public function saveNotFound()
    {
        try {
            $setting           = Setting::where('user_id', session('user')['id'])->first();
            $stockopnamebefore = [
                ["name" => "STOCKOPNAMEID", "Value" => $setting->stockopname_id],
                ["name" => "NOMORBARCODE", "Value" => request('value')],
            ];

            
            if ($this->checkStockOpnameExists($stockopnamebefore)) { //kalau pernah ditagging tidak ditemukan
                return response()->json([
                    "Message" => "Sudah pernah Anda tandai tidak ditemukan!",
                ], 500);
            }
            $id_exists = $this->checkStockOpnameExists($stockopnamebefore);
            if ($id_exists > 0) { // kalau pernah ditagging
                return response()->json([
                    "Message" => "Sudah pernah ditagging!",
                ], 500);
            }
            $so_masalah = array_merge($stockopnamebefore, [
                ["name" => "MASALAH", "Value" => "not found"],
                ["name" => "PREVLOCATIONID", "Value" => $setting->location_id],
                ["name" => "PREV_LOCATION_SHELF_ID", "Value" => $setting->location_shelf_id],
                ["name" => "PREV_LOCATION_RUGS_ID", "Value" => $setting->location_rugs_id],
                ["name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
                ["name" => "CREATEBY", "Value" => session('user')["username"]],
                ["name" => "CREATETERMINAL", "Value" => \Request::ip()],
            ]);
            $res_stock = Http::post($this->url . "?token=" . $this->token . "&op=add&table=STOCKOPNAMEDETAIL&issavehistory=1&ListAddItem=" . urlencode(json_encode($so_masalah)));
            if ($res_stock["Status"] == "Success") {
                return response()->json([
                    "Message" => "Success",
                ], 200);
            } else {
                return response()->json([
                    "Message" => $res_stock["Message"],
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'Message' => 'Failed Save Tagging: '. $e->getMessage(),
            ], 500);
        }
    }

    public function saveLepasTagging()
    {
        try {
            $setting           = Setting::where('user_id', session('user')['id'])->first();
            $stockopnamebefore = [
                ["name" => "STOCKOPNAMEID", "Value" => $setting->stockopname_id],
                ["name" => "NOMORBARCODE", "Value" => request('value')],
            ];
            $id_exists = $this->checkStockOpnameExists($stockopnamebefore);

            $collections = [
                ["name" => "LOCATION_ID", "Value" => ''],
                ["name" => "LOCATION_SHELF_ID", "Value" => ''],
                ["name" => "LOCATION_RUGS_ID", "Value" => ''],
                ["name" => "UPDATEBY", "Value" => session('user')['username']],
                ["name" => "UPDATETERMINAL", "Value" => \Request::ip()],
                ["name" => "UPDATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
            ];
            $stockopname = [
                ["name" => "STOCKOPNAMEID", "Value" => $setting->stockopname_id],
                ["name" => "COLLECTIONID", "Value" => request('id')],
                ["name" => "NOMORBARCODE", "Value" => request('value')],
                ["name" => "PREVLOCATIONID", "Value" => request('location_id')],
                ["name" => "PREV_LOCATION_SHELF_ID", "Value" => request('location_shelf_id')],
                ["name" => "PREV_LOCATION_RUGS_ID", "Value" => request('location_rugs_id')],
                ["name" => "MASALAH", "Value" => "lepas tagging"],
                ["name" => "CURRENTLOCATIONID", "Value" => ''],
                ["name" => "CURRENT_LOCATION_SHELF_ID", "Value" => ''],
                ["name" => "CURRENT_LOCATION_RUGS_ID", "Value" => ''],
                ["name" => "CREATEDATE", "Value" => now()->addHours(7)->format('Y-m-d H:i:s')],
                ["name" => "CREATEBY", "Value" => session('user')["username"]],
                ["name" => "CREATETERMINAL", "Value" => \Request::ip()],
            ];
            if($id_exists > 0 ){
                $res_col   = Http::post($this->url . "?token=" . $this->token . "&id=" . request('id') . "&op=update&table=COLLECTIONS&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($collections)));
                $res_stock = Http::post($this->url . "?token=" . $this->token . "&id=$id_exists&op=update&table=STOCKOPNAMEDETAIL&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($stockopname)));
            } else {
                $res_col   = Http::post($this->url . "?token=" . $this->token . "&id=" . request('id') . "&op=update&table=COLLECTIONS&issavehistory=1&ListUpdateItem=" . urlencode(json_encode($collections)));
                $res_stock = Http::post($this->url . "?token=" . $this->token . "&op=add&table=STOCKOPNAMEDETAIL&issavehistory=1&ListAddItem=" . urlencode(json_encode($stockopname)));
            }
            if($res_col["Status"] == "Success") {
                if ($res_stock["Status"] == "Success") {
                    return response()->json([
                        "Message" => "Success",
                    ], 200);
                } else {
                    return response()->json([
                        "Message" => $res_stock["Message"],
                    ], 500);
                }
            } else {
                return response()->json([
                    "Message" => $res_col["Message"],
                ], 500); 
            }
        } catch (\Exception $e) {
            return response()->json([
                'Message' => 'Failed Save Tagging: '. $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $setting = Setting::updateOrCreate(['user_id' => session('user')['id']])->refresh();
        return view('tagging', [
            'title'   => 'Tagging',
            'setting' => $setting,
        ]);
    }

    public function checkStockOpnameExists($data)
    {
        $kriteriaFilter = [];
        foreach ($data as $d) {
            $kriteriaFilter[] = array_merge($d, ["KriteriaType" => "Tepat"]);
        }
        $res = Http::get($this->url . "?token=" . $this->token . "&id=" . request('id') . "&op=getlist&table=STOCKOPNAMEDETAIL&KriteriaFilter=" . urlencode(json_encode($kriteriaFilter)));
        if (isset($res["Data"]["Items"][0])) {
            return intval($res["Data"]["Items"][0]["ID"]);
        } else {
            return 0;
        }

    }
}
