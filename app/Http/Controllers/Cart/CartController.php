<?php

namespace App\Http\Controllers\Cart;

use App\Model\cartModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\goodsModel;
class CartController extends Controller
{
    public $id=0;
    public function __construct()
    {
        $this->middleware(function($request,$next){
           $this->id=session()->get('id');
            return $next($request);
        });
    }
    //购物车列表
    public function index(Request $request){
        //echo "欢迎回来";
//        $goods=session()->get("goods_name");
//        if(empty($goods)){
//            echo "购物车是空的";
//        }else{
//            //echo "1";
//            foreach($goods as $v){
//                $goodsInfo=goodsModel::where(['goods_id'=>$v])->first()->toArray();
//                //dump($goodsInfo);
//            }
//        }
        $where=[
            "u_id"=>$this->id
        ];
        $cart_goods=cartModel::where($where)->get()->toArray();
        if(empty($cart_goods)){
            die("购物车是空的");
        }else{
            foreach($cart_goods as $k=>$v){
                $goodsInfo=goodsModel::where(["goods_id"=>$v["goods_id"]])->first()->toArray();
                $goodsInfo['num']=$v['num'];
                $goodsInfo['c_time']=$v['c_time'];
                $goodsInfo['c_id']=$v['c_id'];
                $list[]=$goodsInfo;
            }
        }
        $data=[
            "list"=>$list
        ];
        //var_dump($data);die;
        return view("Cart.list",$data);

    }
    //添加商品
    public function add($goods_id){
        //判断商品是否存在
        $goods=session()->get("cart_goods");
        if(!empty($goods)){
            if(in_array($goods_id,$goods)){
              exit('商品已存在');
            }
        }
        session()->push("cart_goods",$goods_id);
        $where=[
            'goods_id'=>$goods_id
        ];
        $num=goodsModel::where($where)->value("num");
        //echo $num;die;
        if($num<=0){
            echo "库存不足";
            exit;
        }
        $res=goodsModel::where($where)->decrement("num");
        if($res){
            echo "添加成功";
            //header("refresh:2,/cart");
        }
    }
    //添加商品到购物车
    public function insert(Request $request){
        $num=$request->input("num");
        $goods_id=$request->input("goods_id");
        //echo $num;die;
        //检查库存
        $where=[
            'goods_id'=>$goods_id
        ];
        $number=goodsModel::where($where)->value("num");
        //echo $number;die;
        if($number<=0){
            $response=[
                "errno"=>5001,
                "msg"=>"库存不足"
            ];
            return $response;
        }
        //添加到购物车
        $data=[
            "goods_id"=>$goods_id,
            'num'=>$num,
            "c_time"=>time(),
            "u_id"=>session()->get("id"),
            "session_token"=>session()->get("u_token")
        ];
        $cart_id=cartModel::insertGetId($data);
        if(!$cart_id){
            $response=[
                "errno"=>5002,
                "msg"=>"添加购物车失败，请重试"
            ];
            return $response;
        }
        $response=[
            "errno"=>0,
            "msg"=>"添加成功"
        ];
        return $response;
    }
    //删除商品
    public function del($goods_id){
        $goods=session()->get("cart_goods");
        //var_dump($goods);die;
            //执行删除
        foreach($goods as $k=>$v){
            //echo $v;die;
            if($goods_id==$v){
                session()->pull("cart_goods.".$k);
            }
        }
    }
    //购物车列表删除商品
    public function del2($c_id){
        $where=[

            "c_id"=>$c_id
        ];
        $res=cartModel::where($where)->delete();
        if($res){
            echo "删除成功";
            header("refresh:1,/cart");
        }else{
            echo "删除失败";
        }
    }
    //购物车列表
    //    public function cartshow(){
    //        $cartList=cartModel::all();
    //        $data=[
    //            "cartList"=>$cartList
    //        ];
    //        return view("Goods.list",$data);
    //    }
    //退出
    public function quit(){
        session::queue('name', null);
        return view("user/userlogin");
    }



}
