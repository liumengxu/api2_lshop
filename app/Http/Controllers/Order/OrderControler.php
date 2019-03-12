<?php

namespace App\Http\Controllers\Order;

use App\Model\cartModel;
use App\Model\goodsModel;
use App\Model\orderModel;
use App\Model\detailModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class OrderControler extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        echo "1";
    }
    //下订单
    public function add(Request $request){
        //查询购物车商品
        $cart=cartModel::where(['u_id'=>session()->get("id")])->get()->toArray();
        //print_r($cart);die;
        if(empty($cart)){
            echo "订单中没有此商品";
        }
        $order_amount=0;
        $order_number=orderModel::generateOrderSN();
        $u_id=session()->get("id");
        //补全订单详情表
        foreach($cart as $k=>$v){
            $goodsInfo=goodsModel::where(['goods_id'=>$v['goods_id']])->first()->toArray();
            $goodsInfo["order_number"]=$order_number;
            $goodsInfo["u_id"]=$u_id;
            $goodsInfo["buy_number"]=$v["num"];
            $goodsInfo["status"]=$v["status"];
            unset($goodsInfo["num"]);
            $list[]=$goodsInfo;
            $order_amount+=$v['num']*$goodsInfo['price'];
        }
//        var_dump($list);die;
        //生成订单号
        $data=[
            "order_number"=>$order_number,
            "u_id"=>session()->get("id"),
            "c_time"=>time(),
            "order_amount"=>$order_amount
        ];
        //print_r($data);die;
        $res=orderModel::insertGetId($data);
        $res2=detailModel::where(["order_number"=>$order_number])->insert($list);
        //var_dump($res2);die;
        if($res && $res2){
            echo "订单生成".$order_number;
            header("refresh:2,/order/orderList");
        }else{
            echo "订单生成失败";
        }
        //清空购物车
        CartModel::where(['u_id'=>session()->get('id')])->delete();
    }
    //订单列表
    public function orderList(){
        $where=[
            "u_id"=>session()->get('id')
        ];
        $orderInfo=orderModel::where($where)->get()->toArray();
        $detailInfo=detailModel::get()->toArray();
       //print_r($detailInfo);die;
        if(empty($orderInfo)){
            echo "该用户下没有订单";
            die;
        }
        if($orderInfo){
            $data=[
                "orderInfo"=>$orderInfo
            ];
            return view("Order.list",$data);
        }
    }
    //订单详情
    public function orderDetail($order_number){
        $detailInfo=detailModel::where(["order_number"=>$order_number])->get()->toArray();
        if(empty($detailInfo)){
            echo "没有订单信息";
        }
        if($detailInfo){
            $data=[
                "detailInfo"=>$detailInfo,
                "order_number"=>$order_number,
                "status"=>$detailInfo[0]["status"]
            ];
            return view("Order.detail",$data);
        }
    }
    //取消订单
    public function del($order_number,$status){
        $where=[
          "order_number"=>$order_number
        ];
        $data=[
          "status"=>3
        ];
        $res=orderModel::where($where)->update($data);
        $res2=detailModel::where($where)->update($data);
        if($res&&$res2){
            //归还库存
            $detailInfo=detailModel::where($where)->get()->toArray();
            foreach($detailInfo as $k=>$v){
                $goodsInfo=goodsModel::where(["goods_id"=>$v["goods_id"]])->first()->toArray();
                $goodsInfo["num"]=$goodsInfo['num']+$v["buy_number"];
                $res=goodsModel::where(["goods_id"=>$v["goods_id"]])->update($goodsInfo);
            }
            if($res){
                echo "成功";
            }else{
                echo "失败";
            }
            echo "订单取消成功";
            header("refresh:2,/order/orderList");


        }else{
            echo "订单取消失败";
            header("refresh:2,/order/orderList");
        }
    }
    //去支付
    public function pay($order_number,$status){
        $orderInfo=orderModel::where(["order_number"=>$order_number])->get()->toArray();
        if($orderInfo){
            //echo "支付成功";
            $orderInfo=orderModel::first()->toArray();
            $where=[
                "order_number"=>$order_number
            ];
            $data=[
                "status"=>2
            ];
            $res=orderModel::where($where)->update($data);
            //var_dump($res);die;
            if($res){
                echo "成功.";
            }else{
                echo "失败";
            }
        }else{
            echo "支付失败";
        }
    }
    //解藕
    public function jieou(){
        $url="http://www.xue.tactshan.com";
        $client=new Client(['base_uri'=>$url,'timeout'=>2.0,]);
        $response=$client->request('GET','/order.php');
        echo $response->getBody();
    }
}
