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
        $where = " WHERE CREATEBY = '" .session('user')['username']. "'";
        $end = $start + $length;
        $sql = "";
        $periode = 'DD';
        $periode2 = 'DD';

        switch(strtolower($request->input('periode'))){
            case 'harian': 
                $periode = 'DD'; $periode2 = 'DD';
                $where .=" AND createdate >= SYSDATE -7 ";
                break;
            case 'bulanan': 
                $periode = 'YYYY-MM'; $periode2 = 'mon';
                $where .= " AND to_char(createdate,'YYYY') = '".now()->year."'";
                break;
            case 'tahunan': 
                $periode = 'YYYY'; $periode2 = 'YYYY';
                break;
            default: 
                $sql = "";
            break;
        }   
        $sql = "SELECT count(1) total,  count(case when masalah = 'metadata' then 1 end) as jml_metadata,
            count(case when masalah = 'not found' then 1 end) as jml_notfound,
            count(case when masalah is null  then 1 end) as jml_tagging, TO_CHAR(createdate,'$periode'), TO_CHAR(createdate,  '$periode2') periode
            FROM stockopnamedetail
            $where 
            GROUP BY  TO_CHAR(createdate,'$periode'), TO_CHAR(createdate,  '$periode2')
            ORDER BY  TO_CHAR(createdate,'$periode') asc
         ";
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
                    $val['JML_METADATA'],
                    $val['JML_NOTFOUND'],
                    $val['JML_TAGGING'],
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
