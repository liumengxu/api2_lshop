<?php

namespace App\Http\Controllers\Type;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TypeController extends Controller
{
    public function type(Request $request){
        return view("type.list");
       // $type=$request->input();
    }
    public function typePdf(Request $request){
        $pdf=$request->file("type");
        //print_r($pdf);
        $ext=$pdf->extension();
        if($ext!="pdf"){
            echo "请上传pdf格式";
        }
        $res=$pdf->storeAs(date('Ymd'),str_random(5) . '.pdf');
        if($res){
            echo "上传成功";
        }
    }
}
