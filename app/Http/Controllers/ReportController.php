<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    { 
        return view('report', [
            'title' => 'Laporan'
        ]);
    }

    public function datatable(Request $request)
    {
        $start  = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search.value');
        $where = "";
        $end = $start + $length;
        $sql = "";
        switch(strtolower($request->input('periode'))){
            case 'harian': 
                $sql = "SELECT count(1) total, TO_CHAR(createdate, 'DD') periode from STOCKOPNAMEDETAIL
                    where createdate >= SYSDATE -7 
                    group by TO_CHAR(createdate, 'DD')
                    order by TO_CHAR(createdate, 'DD') asc";
                break;
            case 'bulanan': 
                $sql = "SELECT count(1) total, TO_CHAR(createdate, 'mon') periode, TO_CHAR(createdate, 'MM') from STOCKOPNAMEDETAIL
                group by TO_CHAR(createdate, 'mon'), TO_CHAR(createdate, 'MM') 
                order by TO_CHAR(createdate, 'MM') asc";
                break;
            case 'tahunan': 
                $sql = "SELECT count(1) total, TO_CHAR(createdate, 'YYYY') periode from STOCKOPNAMEDETAIL
                group by TO_CHAR(createdate, 'YYYY')
                order by TO_CHAR(createdate, 'YYYY') asc";
                break;
            default: 
                $sql = "";
            break;
        }
       
       
        $totalData = kurl("get","getlistraw", "", "SELECT COUNT(1) JUMLAH  from ($sql) ", 'sql', '')["Data"]["Items"][0]["JUMLAH"];
        if($length == '-1'){
            $end = $totalData;
        }
        $queryData = kurl("get","getlistraw", "",  "SELECT outer.* FROM (SELECT ROWNUM rn, inner.* FROM ($sql )  inner WHERE rownum <=$end) outer WHERE rn >$start", 'sql', '')["Data"]["Items"];
        $totalFiltered = kurl("get","getlistraw", "",  "SELECT COUNT(1) JUMLAH FROM ($sql )", 'sql', '')["Data"]["Items"][0]["JUMLAH"];
        $totalCount = kurl("get","getlistraw", "", "SELECT SUM(total) JUMLAH FROM ($sql) ", 'sql', '')["Data"]["Items"][0]["JUMLAH"];
        $response['data'] = [];
        if ($queryData <> FALSE) {
            $nomor = $start + 1;
            foreach ($queryData as $val) {
                $response['data'][] = [
                    $val['PERIODE'],
                    $val['TOTAL'],
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
        $response["grandTotal"] = $totalCount;

        return response()->json($response);
    }
}
