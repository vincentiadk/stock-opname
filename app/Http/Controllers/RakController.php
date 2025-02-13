<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RakController extends Controller
{
    public function index()
    {
        return view('rak', [
            'title' => 'Data Rak'
        ]);
    }

    public function datatable(Request $request)
    {
        $start  = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search.value');
        $where = "";
        $end = $start + $length;
        $sql = "SELECT * from collections where location_id = 341 ";
        $sqlFiltered = "SELECT 1 from collections where location_id = 341 ";
       
        if($request->input('location_id') !=''){
            $where .= " AND location_id = '".$request->input('location_id')."'";
        }

        $totalData = kurl("get","getlistraw", "", "SELECT COUNT(1) JUMLAH  from collections where location_id = 341 ", 'sql', '')["Data"]["Items"][0]["JUMLAH"];
        if($length == '-1'){
            $end = $totalData;
        }
        $queryData = kurl("get","getlistraw", "",  "SELECT outer.* FROM (SELECT ROWNUM rn, inner.* FROM ($sql )  inner WHERE rownum <=$end) outer WHERE rn >$start", 'sql', '')["Data"]["Items"];
        $totalFiltered = kurl("get","getlistraw", "",  "SELECT COUNT(1) JUMLAH FROM ($sqlFiltered )", 'sql', '')["Data"]["Items"][0]["JUMLAH"];

        $response['data'] = [];
        if ($queryData <> FALSE) {
            $nomor = $start + 1;
            foreach ($queryData as $val) {
                $response['data'][] = [
                    //$nomor,
                    explode('/', $val['TITLE'])[0],
                    $val['PUBLISHYEAR'],
                    $val['NOMORBARCODE'],
                    $val['NOINDUK_DEPOSIT']
                ];
                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if ($totalData <> FALSE) {
            $response['recordsTotal'] = $totalData;
        }

        $response['recordsFiltered'] = 0;
        if ($totalFiltered <> FALSE) {
            $response['recordsFiltered'] = $totalFiltered;
        }

        return response()->json($response);
    }
}
