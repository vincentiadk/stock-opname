<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TaggingController extends Controller
{
    protected $url;
    protected $token;

    public function __construct() 
    {
        $this->url = config('inlis.url');
        $this->token = config('inlis.token');
    }
    public function searchItem(Request $request)
    {
        try{
            $sql = "SELECT * FROM COLLECTIONS WHERE nomorbarcode='" . $request->input('barcode') ."'";
            $res = Http::post($this->url ."?token=$this->token&op=getlistraw&sql=$sql");
            $response = $res->json();
            if($response["Status"] == "Success") {
                if(isset($response["Data"]["Items"][0])){
                    return response()->json(
                        [
                            "Status" => "Success",
                            "Data" => $response["Data"]["Items"][0],
                            "Message" => $response["Message"]
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            "Status" => "Error",
                            "Message" => $request->input('barcode') . " tidak ditemukan",
                        ]
                    );
                }
                
            } else {
                return response()->json([
                    "Status" => "Error",
                    "Message" => $request->input('barcode') . " tidak ditemukan",
                ], 500);
            }
        }catch (\Exception $e){
            return response()->json([
                'message'   => 'Failed Search Collection.',
                'err'       => $e->getMessage(),
                'status'    => 'Failed'
            ], 500);
        }
    }

    public function saveTagging()
    {
        try {
            $params = [
                ["name" => "LOCATION_ID", "Value" => $loc_id],
                ["name" => "COLLECTION_ID", "Value" => request('collection_id')],
                ["name" => "KETERANGAN", "Value" => $keterangan],
                ["name" => "CREATEBY", "Value" => '<b>' . request('title') . '</b>'],
                ["name" => "CREATETERMINAL", "Value" => substr($authors, 0, 2000) ],
                ["name" => "CREATEDATE", "Value" => request('bulan_terbit') . '-' . request('tahun_terbit')],
            ];
        }catch (\Exception $e){
            return response()->json([
                'message'   => 'Failed Save Tagging',
                'err'       => $e->getMessage(),
                'status'    => 'Failed'
            ], 500);
        }
    }

    public function index()
    {
        return view('tagging', [
            'title' => 'Tagging'
        ]);
    }
}
