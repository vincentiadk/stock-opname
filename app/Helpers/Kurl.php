<?php

use Illuminate\Support\Facades\Http;
/*
params kurl
$method = post/get
$action = add, getlist, update, delete
$table = table yang akan dieksekusi
$data = data filter atau data update atau data add yang berupa array
$kategori = dari backend ada ListAddItem, ListUpdateItem
$params = untuk penambahan params pada saat req api (pagination dll)
*/

function kurl($method, $action, $table, $data, $kategori, $params = null) { 
    $body = $action == 'getlistraw' ? $data : json_encode($data);
    $form_data = [
        'token' => config('inlis.token'),
        'op' => $action,
        'table' => $table,
        $kategori => $body
    ];

    //page
    if (!empty($params)) {
        $form_data = array_merge($form_data, $params);
    }
    $response = Http::asForm()->$method(config('inlis.url'), $form_data);
    if ($response->successful()) {
        $data = $response->json();
        return $data;

    } else {
        // Handle the error
        $status = $response->status();
        $error = $response->body();
        return $status;
    }
}            


