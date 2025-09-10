<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class ShelvingController extends BaseController
{
    public function index()
    {
        $setting = Setting::updateOrCreate(['user_id' => session('user')['id']])->refresh();
        return view('shelving', [
            'title'   => 'Penjajaran Koleksi',
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
                [ "name" => "STATUS", 'Value' => 'PENDING' ],
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

    public function synchronize(Request $request, $id = null)
    {
        try {
            // Ambil ID dari route param atau query/body
            $id = $id ?? $request->route('id') ?? $request->input('id');
            if (!$id) {
                return response()->json([
                    'Message' => 'Parameter id wajib diisi.'
                ], 422);
            }

            $baseUrl = rtrim(config('services.fastapi.base_url', env('FASTAPI_BASE_URL', '')), '/');
            if (!$baseUrl) {
                return response()->json([
                    'Message' => 'FASTAPI_BASE_URL belum dikonfigurasi.'
                ], 500);
            }

            // Siapkan client dengan optional Bearer token
            $client = Http::retry(3, 300)      // 3x retry, jeda 300ms
                          ->timeout(20);       // timeout 20 detik

            $token = config('services.fastapi.token', env('FASTAPI_TOKEN'));
            if (!empty($token)) {
                $client = $client->withToken($token);
            }

            // Panggil FastAPI (POST /sync/{id}) — ganti ke GET jika endpoint Anda GET
            $response = $client->post("{$baseUrl}/sync/{$id}", [
                // kirimkan konteks tambahan bila perlu:
                'requested_by' => session('user')['username'] ?? 'system',
                'request_ip'   => $request->ip(),
            ]);

            if ($response->successful()) {
                return response()->json([
                    'Message' => 'Sync request sent.',
                    'Data'    => $response->json(),
                ], 200);
            }

            // Gagal (4xx/5xx)
            return response()->json([
                'Message' => 'FastAPI error.',
                'Status'  => $response->status(),
                'Error'   => $response->json() ?? $response->body(),
            ], 502);

        } catch (\Throwable $e) {
            return response()->json([
                'Message' => 'Unexpected error.',
                'Error'   => $e->getMessage(),
            ], 500);
        }
    }
}
