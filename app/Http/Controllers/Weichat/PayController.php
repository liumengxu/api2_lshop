<?php

namespace App\Http\Controllers\Weichat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\orderModel;


class PayController extends Controller
{
    public $weixin_unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    public $weixin_notify_url = 'http://xue.qianqianya.xyz/weichat/notice';     //支付通知回调
    //下订单
    public function order($order_number){
        $total_fee=1;  //订单总金额是1分
        //$order_id=orderModel::generateOrderSN();   //生成订单号
        //var_dump($order_id);die;
        $order_info=[
            "appid"=>env('WEIXIN_APPID_0'),  //微信支付分配的公众账号ID（企业号corpid即为此appId）
            "mch_id"=>env('WEIXIN_MCH_ID'),  //微信支付分配的商户号
            "nonce_str"=>str_random(16),   //随机字符串
            "sign_type"=>'MD5',  //签名类型
            "body"=>'测试刘梦雪订单-'.$order_number,  //商品描述
            "out_trade_no"=>$order_number,
            "total_fee"=>$total_fee,
            "spbill_create_ip"=>$_SERVER['REMOTE_ADDR'], //客户端ip
            "notify_url"=>$this->weixin_notify_url, //异步通知回调地址
            "trade_type"=>"NATIVE"  //交易类型
        ];
        $this->values=[];
        $this->values=$order_info;
        $this->SetSign();
        $xml = $this->ToXml();      //将数组转换为XML
        $rs = $this->postXmlCurl($xml, $this->weixin_unifiedorder_url, $useCert = false, $second = 30);
        $data =  simplexml_load_string($rs);
        //var_dump($data);
        $code_url=$data->code_url;
        //echo $code_url;exit;
        $info=[
            'code_url'=>$code_url,
            'order_number'=>$order_number
        ];
        return view('weixin.qrcode',$info);
    }
//    public function code(){
//        return view('weixin.qrcode');
//    }

    //订单支付成功
    public function success(){
        $order_number=$_POST["order_number"];
        $where=[
            "order_number"=>$order_number
        ];
        $data=orderModel::where($where)->first()->toArray();
        //var_dump($data);exit;
        if($data['status']==2){
            echo 1;
        }
    }

    protected function ToXml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            die("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    private  function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误码:$error");
        }
    }

    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }
    private function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".env('WEIXIN_MCH_KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    protected function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    //微信支付回调
    public function notice(){
        $data=file_get_contents("php://input");
        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_pay_notice.log',$log_str,FILE_APPEND);
        $xml = (array)simplexml_load_string($data,'SimpleXMLElement',LIBXML_NOCDATA);

        if($xml["result_code"]=='SUCCESS' && $xml["return_code"]=='SUCCESS'){      //微信支付成功回调
            //验证签名
            //$sign = true;
            $this->values=[];
            $this->values=$xml;
            $sign=$this->SetSign();
            //echo $sign;die;
            if($sign==$xml["sign"]){       //签名验证成功
                $this->upOrder($xml);

            }else{
                echo '验签失败，IP: '.$_SERVER['REMOTE_ADDR'];  //记录日志
            }
        }
        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;

    }
    //验签
//    public function sign(){
//
//    }
    //处理订单逻辑  更改订单状态
    public function upOrder($xml){
        $order_number=$xml["out_trade_no"];
        $where=[
            "order_number"=>$order_number,
            "status"=>1
        ];
        $info=orderModel::where($where)->first();
        if(!$info){
            $data=file_get_contents("php://input");
            //记录日志
            $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
            file_put_contents('logs/wx_orderpay_notice.log',$log_str,FILE_APPEND);
            echo "该订单不存在或者已被支付";
        };
        $data=[
            "status"=>2
        ];
        $order_info=orderModel::where($where)->update($data);
        if($order_info){
            $data=file_get_contents("php://input");
            //记录日志
            $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
            file_put_contents('logs/wx_orderpay_notice.log',$log_str,FILE_APPEND);
            echo "订单已支付成功";
        };
    }
}
