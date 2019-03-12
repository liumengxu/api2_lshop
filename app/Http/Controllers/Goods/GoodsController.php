<?php

namespace App\Http\Controllers\Goods;

use App\Model\goodsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

class goodsController extends Controller
{
    public  function index($goods_id){
        $where=[
            "goods_id"=>$goods_id
        ];
        $goods=goodsModel::where($where)->first();
        if(!$goods){
            echo "商品不存在";
        }else{
            $data=[
                "goods"=>$goods
            ];
            return view("Goods.index",$data);
        }
    }
    //商品列表
    public function goodsshow(Request $request){
        $name=Input::get('name');
        var_dump($name);
        $newlist=goodsModel::where('name', 'like', "%$name%")->get();
        $list=goodsModel::paginate(3);
        $data=[
                "list"=>$list,
                "name"=>$name
            ];
        return view("Goods.list",$data);
        //return view("Goods.list",$name);
    }

}
