<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\UserModel;

class UserController extends Controller
{
    //

	public function user($uid)
	{
		echo $uid;
	}

	public function test()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
    }

	public function add()
	{
		$data = [
			'name'      => str_random(5),
			'age'       => mt_rand(20,99),
			'email'     => str_random(6) . '@gmail.com',
			'c_time'  => time()
		];

		$id = UserModel::insertGetId($data);
		var_dump($id);
	}
	//用户注册
	public function reg(){
		return view("user.reg");
	}
	public function doreg(Request $request){
//		echo __METHOD__;
		//echo print_r($_POST);
		$pass=$request->input('pwd');
		$pwd=password_hash($pass,PASSWORD_BCRYPT);
		$data=[
			'name'=>$request->input('uname'),
			'pwd'=>$pwd,
			'age'=>$request->input('age'),
			'email'=>$request->input('email'),
			'c_time'=>time()
		];
		$where=[
			'name'=>$request->input('uname'),
		];
		//print_r($where);die;
		$res=UserModel::where($where)->first();
		if($res){
			echo "账号已存在不能注册";
			header("refresh:1,/userreg");
		}else{
			$add=UserModel::insertGetId($data);
			setcookie('id',$add,time()+86400,'/','shops.com',false,true);
			echo "注册成功，正在跳转";
			header("refresh:1,/userlogin");
		}
	}
	//用户登录
	public function login(){
		return view("user.login");
	}
	//微信登录
	public function weixinLogin(){
		return view("user.login");
	}
	public function dologin(Request $request){
		$name=$request->input('uname');
		$pwd=$request->input('pwd');
		$where=[
			'name'=>$name
		];
		$res=UserModel::where($where)->first();
		if($res){
			if(password_verify($pwd,$res->pwd)){
				$token=substr(md5(time().mt_rand(1,9999)),10,10);
//				setcookie('id',$res->id,time()+86400,'/','',false,true);
				$request->session()->put('id',$res->id);
				setcookie('token',$token,time()+86400,'/','',false,true);
				//var_dump($_COOKIE['token']);die;
				//print_r($_COOKIE);
				$request->session()->put('u_token',$token);
				echo "登录成功";
				header("refresh:1,/usercenter");
			}else{
				echo "账号或密码错误";
				header("refresh:1,/userlogin");
			}
		}else{
			echo "账户不存在";
			header("refresh:1,/userlogin");
		}
	}
	//用户中心
	public function center(Request $request){
			//echo "ADD".$_COOKIE['id']."欢迎回来";
			echo "欢迎登录";
			header("refresh:1,/cart");
}
}
