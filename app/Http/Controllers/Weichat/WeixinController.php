<?php

namespace App\Http\Controllers\Weichat;

use App\Model\WeixinModel;
use App\Model\MessageModel;
use App\Model\WxModel;
use App\Model\UserModel;
use App\Model\MaterialModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;

class WeixinController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token
    protected $redis_weixin_jsapi_ticket = 'str:weixin_jsapi_ticket';     //微信 jsapi_ticket

    public function test()
    {
        //echo __METHOD__;
        echo 'Token: '. $this->getWXAccessToken();
    }
    //首次接进去
    public function validToken1(){
        echo $_GET['echostr'];
    }
    //接收微信事件的推送
    public function validToken(){
        $data=file_get_contents("php://input");
        //var_dump($data);exit;
        //解析XML
        $xml=simplexml_load_string($data); //将字符串转化成数组
        $log_str=date('Y-m-d H:i:s')."\n".$data."\n<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
        $event=$xml->Event;  //事件类型
        $openid=$xml->FromUserName;  //用户openid
        //处理用户发送消息
        if(isset($xml->MsgType)){
            if($xml->MsgType=="text"){  //接收文本消息
                $msg=$xml->Content;
                //记录聊天消息
                $data = [
                    'msg'       => $xml->Content,
                    'msgid'     => $xml->MsgId,
                    'openid'    => $openid,
                    'msg_type'  => 1 ,       // 1用户发送消息 2客服发送消息
                    'send_time'=>time()
                ];
                //var_dump($data);
                $id = MessageModel::insertGetId($data);
                //var_dump($id);
//                $xml_response='<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. $msg. date('Y-m-d H:i:s') .']]></Content></xml>';
//                echo $xml_response;
//                exit;
            }elseif($xml->MsgType=="image"){  //用户发送图片信息
                if(1){ //下载图片素材
                    $file_name=$this->dlWxImg($xml->MediaId);
                    $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.'你好，欢迎来到这里'. date('Y-m-d H:i:s') .']]></Content></xml>';
                    echo $xml_response;
                    //写入数据库
                    $data = [
                        'openid'    => $openid,
                        'add_time'  => time(),
                        'msg_type'  => 'image',
                        'media_id'  => $xml->MediaId,
                        'format'    => $xml->Format,
                        'msg_id'    => $xml->MsgId,
                        'local_file_name'=> $file_name
                    ];
                    $m_id = WxModel::insertGetId($data);
                    var_dump($m_id);
                }
            }elseif($xml->MsgType=="voice"){   //接收语音信息
                $this->dlWxVoice($xml->MediaId);
            }
        }
        if($event=="subscribe"){
            $openid=$xml->FromUserName;
            $sub_time = $xml->CreateTime;
            echo 'openid: '.$openid;echo '</br>';
            echo '$sub_time: ' . $sub_time;
            //获取用户信息
            $user_info=$this->getUserInfo($openid);
            print_r($user_info);
            //保存用户信息
            $user=WeixinModel::where(['openid'=>$openid])->first();
            if($user){
                echo "该用户已存在";
            }else{
                $user_data=[
                    "openid"=>$openid,
                    "add_time"=>time(),
                    "nickname"=>$user_info["nickname"],
                    "sex"=>$user_info["sex"],
                    "headimgurl"=>$user_info["headimgurl"],
                    "subscribe_time"=>$sub_time,
                ];
                $u_id=WeixinModel::insertGetId($user_data); //保存用户信息
                var_dump($u_id);
                }
            }else if($event=="CLICK"){ //click菜单
                if($xml->EventKey=="kefu01"){
                    $this->kefu01($openid,$xml->ToUserName);
                }

        }

    }
    //接收自动回复的信息
    public function kefu01($openid,$from)
    {
        // 文本消息
        $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$from.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. '你好，小事请找我，大事请拨打110   '. date('Y-m-d H:i:s') .']]></Content></xml>';
        echo $xml_response;
    }

    //下载图片素材
    public function dlWxImg($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;
        //保存图片
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        //print_r($file_info);die;
        $file_name = substr(rtrim($file_info[0],'"'),-20);
        $wx_image_path = 'wx/images/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功
            return true;
        }else{      //保存失败
            return false;
        }
        return $file_name;
    }
    //接收语音消息
    public function dlWxVoice($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;
        //保存图片
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);
        $wx_image_path = 'wx/voice/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功
            return true;
        }else{      //保存失败
            return false;
        }

    }

    //获取微信Access_Token
    public function getWXAccessToken(){
        //得到缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;
    }
    /**
     * 获取用户信息
     * @param $openid
     */
    public function getUserInfo($openid)
    {
        //$openid = 'oLreB1jAnJFzV_8AGWUZlfuaoQto';
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $data = json_decode(file_get_contents($url),true);
        //echo '<pre>';print_r($data);echo '</pre>';
        return $data;
    }
    //群发消息
    public function textGroup(){
        $access_token = $this->getWXAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$access_token;
        $client=new GuzzleHttp\Client(['base_uri'=>$url]);
        $data=[
            'filter'=>[
                'is_to_all'=>true,  //选择true该消息群发给所有用户，选择false可根据tag_id发送给指定群组的用户
                'tag_id'=>2  //is_to_all为true可不填写
            ],
            'text'=>[
                'content'=>'欢迎进入测试阶段'
            ],
            'msgtype'=>'text'
        ];
        $r=$client->request('post',$url,['body'=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
        //解析接口返回信息
        $response_arr=json_decode($r->getBody(),true);
        //var_dump($response_arr);
        if($response_arr['errcode']==0){
            echo "群发成功";
        }else{
            echo "群发失败，请重试";
        }

    }
    //创建服务号菜单
    public function create_menu(){
        //获取access_token的接口
        $access_token=$this->getWXAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        //请求微信接口
        $client=new GuzzleHttp\Client(['base_uri'=>$url]);
        $data=[

            "button"=>[
                [
                    "type"  => "click",      // click类型
                    "name"  => "获取自动回复",
                    "key"   => "kefu01"
                ],
                [
                    "name"  => "拍照",
                    "sub_button"=>[
                    [
                        "type"=>"pic_sysphoto",
                        "name"=> "系统拍照发图",
                        "key"=>"rselfmenu_1_0",
                    ],
                     [
                        "type"=>"pic_photo_or_album",
                        "name"=> "拍照或者相册发图",
                        "key"=>"rselfmenu_1_1",
                    ],
                    [
                        "type"=>"pic_weixin",
                        "name"=>"微信相册发图",
                        "key"=>"rselfmenu_1_2",
                    ]
                 ]
                ],

                [
                    "type"  => "view",      // view类型 跳转指定 URL
                    "name"  => "个人中心",
                    "url"   => "https://www.baidu.com"
                ],
        ]
        ];
        $r=$client->request("POST",$url,["body"=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
        //解析微信接口返回信息
        $response_arr=json_decode($r->getBody(),true);
        if($response_arr['errcode']==0){
            echo "菜单创建成功";
        }else{
            echo "菜单创建失败，请重试";
            echo $response_arr["errmsg"];
        }
    }
    //刷新access_token
    public function refreshToken()
    {
        Redis::del($this->redis_weixin_access_token);
        echo $this->getWXAccessToken();
    }

    //测试
    public function formshow(){
        return view("test.test");
    }

    //上传素材  测试
    public function upMateriaTest($file_path){
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';

        $client = new GuzzleHttp\Client();
        $response=$client->request('POST',$url,[
            'multipart'=>[
                [
                    "name"=>'media',
                    'contents'=>fopen($file_path,'r')
                ],
            ]
        ]);
        $body=$response->getBody();
        //echo $body;
        $d=json_decode($body,true);
        //print_r($d);
    }

   //获取永久素材列表
    public function fileshow(Request $request){
        //print_r($_POST);  //Array ( [_token] => 8pdO4mrG7gTGg0Qy3HVsLyjyZM66zRxSRKaIxDEA )
       // print_r($_FILES);  //Array ( )
        //保存文件  测试
        $img_file=$request->file('media');
        //print_r($img_file);exit;
        $img_orign_name=$img_file->getClientOriginalName();
        //print_r($img_orign_name);
        $file_ext=$img_file->getClientOriginalExtension();  //得到扩展名字
        //print_r($file_ext);exit;
        $new_file_name=str_random(15).'.'.$file_ext;  //重命名
        print_r($new_file_name);
       //文件保存路径
        $save_file_path=$request->media->storeAs("fileshow",$new_file_name);
        echo $save_file_path;
        //上传微信到永久素材
       $this->upMaterial($save_file_path);
    }


    //上传素材
    public function upMaterial($file_path)
    {
        //https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN&type=TYPE
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';
        $client = new GuzzleHttp\Client();
        //var_dump($client);exit;
        $response = $client->request('POST',$url,[
            'multipart' => [
                [
                    'name'     => 'username',
                    'contents' => 'ceshi'
                ],
                [
                    'name'     => 'media',
                    'contents' => fopen($file_path, 'r')
                ],
            ]
        ]);
        //var_dump($response);die;
        $body = $response->getBody();
        //echo $body;die;
        $d = json_decode($body,true);
        //print_r($d);exit;
        $data=[
            'media_id'=>$d['media_id'],
            'add_time'=>time(),
            'media_url'=>$d['url']
        ];
        $res=MaterialModel::insertGetId($data);

    }
    //获取永久素材列表
    public function materialList()
    {
        //print_r($_GET);exit;
        $client = new GuzzleHttp\Client();
        //print_r($client);exit;
        $type = $_GET['type'];
        $offset = $_GET['offset'];
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$this->getWXAccessToken();
        $body = [
            "type"      => $type,
            "offset"    => $offset,
            "count"     => 20
        ];
        $response = $client->request('POST', $url, [
            'body' => json_encode($body)
        ]);

        $body = $response->getBody();
        echo $body;
        $arr = json_decode($response->getBody(),true);
        print_r($arr);


    }

    //聊天列表
    //聊天页面
//    public function massage(){
//        return view('messages.chatmessages');
//    }
//    //存储聊天信息
//    public function save(){
//
//    }

    public function massage()
    {
//        $a=$this->validToken();
//        $openid=$a->FromUserName;
        $data = [
            'openid'    => "ot0Xe5xGR3cHjUE2wdLnseTHmO-Y"
        ];
        return view('messages.chat',$data);
    }
    //客服信息
    public function getmessage(){
        $openid=$_GET['openid'];
        //var_dump($openid);exit;
        $msg=$_GET['send_msg'];
        $access_token = $this->getWXAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
        $client=new GuzzleHttp\Client(['base_uri'=>$url]);
        $data=[
            "touser"=>$openid,
            "msgtype"=>'text',
            "text"=>[
                "content"=>$msg
            ],
        ];
        $r=$client->request('post',$url,['body'=>json_encode($data,JSON_UNESCAPED_UNICODE)]);
        //解析接口返回信息
        $response_arr=json_decode($r->getBody(),true);
        //var_dump($response_arr);
        $info=[
            "openid"=>$openid,
            "msg"=>$msg,
            "msg_type"=>2,  //1是用户发送的信息，2是客服发送的消息
            "send_time"=>time()
        ];
        $res=MessageModel::insertGetId($info); //保存用户信息
    }
    public function getChatMsg()
    {
        $openid = $_GET['openid'];  //用户openid
        //var_dump($openid);exit;
        $pos = $_GET['pos'];        //上次聊天位置
        //var_dump($pos);exit;
        $msg = MessageModel::where(['openid'=>$openid])->where('id','>',$pos)->orderby("send_time","desc")->where(["msg_type"=>1])->first();
        //var_dump($msg);exit;
        if($msg) {
            $response = [
                'errno' => 0,
                'data' => $msg->toArray()
            ];
        }else{
            $response = [
                'errno' => 50001,
                'msg'   => '服务器异常，请联系管理员'
            ];
        }
       die( json_encode($response)) ;

    }
    //微信登录
   // public function login(){
    //    return view("user.login");
   // }
    //接收code
   // public function code(Request $request){
     //   $code=$_GET["code"];
        //echo $code;
        //用code换取access_token 请求接口
       // $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxe24f70961302b5a5&secret=0f121743ff20a3a454e4a12aeecef4be&code='.$code.'&grant_type=authorization_code';
       // $token_json = file_get_contents($token_url);
       // $token_arr = json_decode($token_json,true);
        //print_r($token_arr);exit;
       // $access_token = $token_arr['access_token'];
       // $openid = $token_arr['openid'];
        //获取用户信息
       // $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
       // $user_json = file_get_contents($user_info_url);
       // $user_arr = json_decode($user_json,true);
        //echo '<pre>';print_r($user_arr);echo '</pre>';
       // $where=[
         //   "name"=>$user_arr['unionid']
        ];
       // $userinfo=UserModel::where($where)->first();
       // var_dump($userinfo);
       // if(empty($userinfo)){
            //保存到数据库
         //   $data=[
           //     "name"=>$user_arr['unionid'],
             //   "c_time"=>time(),
           // ];
           // $res=UserModel::insertGetId($data);
           // var_dump($res);
           // $token=substr(md5(time().mt_rand(1,9999)),10,10);
          //  $request->session()->put('id',$userinfo->id);
          //  setcookie('token',$token,time()+86400,'/','',false,true);
          //  $request->session()->put('u_token',$token);
           // echo "登录成功";
           // header("refresh:1,/usercenter");
       // }else{
         //   echo "账户不存在,错误";
            //header("refresh:1,/userlogin");
       // }
   // }
    //微信JSSDK
    //计算签名
    public function jssdk(){
        $jsconfig=[
            'appid' => env('WEIXIN_APPID'),        //APPID
            'timestamp' => time(),
            'noncestr'    => str_random(10),
        ];
        $sign = $this->wxJsConfigSign($jsconfig);
        $jsconfig['sign'] = $sign;
        $data = [
            'jsconfig'  => $jsconfig
        ];
        return view("weixin.jssdk",$data);
    }
   //得到签名
    public function wxJsConfigSign($param){
        $current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];     //当前调用 jsapi的 url
        //var_dump($current_url);
        $ticket = $this->getJsapiTicket();
        $str =  'jsapi_ticket='.$ticket.'&noncestr='.$param['noncestr']. '&timestamp='. $param['timestamp']. '&url='.$current_url;
        $signature=sha1($str);
        return $signature;
//        $sign=str_random(15);
//        return $sign;
    }
    //得到jsapi缓存
    public function getJsapiTicket(){
        //是否有缓存
        $ticket = Redis::get($this->redis_weixin_jsapi_ticket);
        if(!$ticket){           // 无缓存 请求接口
            $access_token = $this->getWXAccessToken();
            $ticket_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $ticket_info = file_get_contents($ticket_url);
            $ticket_arr = json_decode($ticket_info,true);

            if(isset($ticket_arr['ticket'])){
                $ticket = $ticket_arr['ticket'];
                Redis::set($this->redis_weixin_jsapi_ticket,$ticket);
                Redis::setTimeout($this->redis_weixin_jsapi_ticket,3600);       //设置过期时间 3600s
            }
        }
        return $ticket;
    }
}
