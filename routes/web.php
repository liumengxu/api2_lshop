<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
    //echo date("Y-m-d H:i:s");
});

Route::get('/adduser','User\UserController@add');

//路由跳转
Route::redirect('/hello1','/world1',301);
Route::get('/world1','Test\TestController@world1');

Route::get('hello2','Test\TestController@hello2');
Route::get('world2','Test\TestController@world2');


//路由参数
Route::get('/user/test','User\UserController@test');
Route::get('/user/add','User\UserController@add');
Route::get('/test','Test\TestController@world1');
//Route::get('/user/{uid}','User\UserController@user');
Route::get('/month/{m}/date/{d}','Test\TestController@md');
Route::get('/name/{str?}','Test\TestController@showName');



// View视图路由
Route::view('/mvc','mvc');
Route::view('/error','error',['code'=>40300]);


// Query Builder
Route::get('/query/get','Test\TestController@query1');
Route::get('/query/where','Test\TestController@query2');


//Route::match(['get','post'],'/test/abc','Test\TestController@abc');
Route::any('/test/abc','Test\TestController@abc');


//展示视图页面  test
Route::get("/test/test1","Test\TestController@viewtest1");
Route::get("/test/test2","Test\TestController@viewtest2");
Route::get("/mvc/test","Mvc\MvcController@test");
Route::get("/test/checkabc","Test\TestController@checkabc")->middleware('checkabc');

//用户注册
Route::get("/userreg","User\UserController@reg");
Route::post("/userreg","User\UserController@doreg");

//用户登录
Route::get("/userlogin","User\UserController@login"); //普通登陆
Route::post("/userlogin","User\UserController@dologin"); //普通登陆
Route::get("/usercenter","User\UserController@center")->middleware("checklogin");


//购物车
Route::get("/cart","Cart\CartController@index")->middleware("checklogin");  //购物车列表
Route::get("/cart/add/{goods_id}","Cart\CartController@add")->middleware("checklogin"); //添加商品
Route::post("/cart/insert","Cart\CartController@insert")->middleware("checklogin");
Route::get("/cart/quit","Cart\CartController@quit")->middleware("checklogin");  //退出
Route::get("/cart/del/{goods_id}","Cart\CartController@del")->middleware("checklogin");  //删除商品
Route::get("/cart/del2/{c_id}","Cart\CartController@del2")->middleware("checklogin");  //删除购物车中商品




//商品
Route::get("/goods/list","Goods\GoodsController@goodsshow")->middleware("checklogin"); //商品列表
Route::get("/goods/index/{goods_id}","Goods\GoodsController@index")->middleware("checklogin");  //商品

//订单表
Route::get("/order/orderList","Order\OrderControler@orderList");  //订单列表
Route::get("/order/detail/{order_number}","Order\OrderControler@orderDetail");  //订单详情
//Route::get("/order/add","Order\OrderControler@add")->middleware("checklogin");  //提交订单
Route::get("/order/add","Order\OrderControler@add");  //提交订单
Route::get("/order/del/{order_number}/{status}","Order\OrderControler@del");  //取消订单
Route::get("/order/pay/{order_number}/{status}","Order\OrderControler@pay");  //支付订单  虚拟支付

//支付页面
Route::get('/pay/alipay/test/{order_number}','Pay\AlipayController@test');         //测试
Route::get('/pay/o/{o_id}','Pay\IndexController@order')->middleware('checklogin');         //订单支付
Route::post('/pay/alipay/notify','Pay\AlipayController@aliNotify');        //支付宝支付 异步通知回调
Route::get('/pay/alipay/return_url','Pay\AlipayController@aliReturn');        //支付宝支付 同步通知回调


//展示页面
//Route::post("/userlist","User\UserController@userlist");

//解藕
Route::get('/order',"Order\OrderControler@jieou");

//文件上传
Route::get("/type","Type\TypeController@type");
Route::post("/typePdf","Type\TypeController@typePdf");


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


//Route::get('/home/admin', 'HomeController@index')->name('home');



//微信
Route::get('/weichat/refresh_token','Weichat\WeixinController@refreshToken');     //刷新token
Route::get('/weichat/test', 'Weichat\WeixinController@validToken1');  //首次接入
Route::get('/weichat/test1', 'Weichat\WeixinController@test');  //得到token
Route::get('/weichat/valid', 'Weichat\WeixinController@validToken1');  //首次接入
Route::post('/weichat/valid', 'Weichat\WeixinController@validToken');  //接收微信事件的推送
Route::get('/weichat/create_menu', 'Weichat\WeixinController@create_menu');  //创建菜单
Route::get('/weichat/textGroup', 'Weichat\WeixinController@textGroup');  //群发消息

//测试
Route::get('/weichat/formshow', 'Weichat\WeixinController@formshow');  //页面展示
Route::post('/weichat/formshow', 'Weichat\WeixinController@fileshow');  //表单测试


//Route::get('/weichat/material/list','Weichat\WeixinController@materialList');     //获取永久素材列表
//Route::post('/weichat/material/upload','Weichat\WeixinController@upMaterial');     //上传永久素材


//微信聊天
Route::get('/weichat/massage', 'Weichat\WeixinController@massage');  //页面展示
Route::get('/weichat/get_msg','Weichat\WeixinController@getChatMsg'); //获取用户聊天信息
Route::get('/weichat/getmessage','Weichat\WeixinController@getmessage'); //客服发送信息

//微信支付
Route::get('/weichat/order/{order_number}','Weichat\PayController@order'); //得到订单信息  支付订单
Route::post('/weichat/notice','Weichat\PayController@notice'); //异步地址回调
Route::post('/weichat/success','Weichat\PayController@success'); //微信二维码支付成功
//Route::get('/weichat/success','Weichat\PayController@successly'); //微信二维码支付成功


//微信登录
//Route::get('/weichat/login','Weichat\WeixinController@login'); //微信登录页面
//Route::get('/weichat/code','Weichat\WeixinController@code'); //获取微信code
//Route::get("/userlogin","Weichat\WeixinController@login"); //微信登陆
//Route::post("/userlogin","Weichat\WeixinController@code"); //微信登陆

//JSSDK
Route::get("/jssdk","Weichat\WeixinController@jssdk"); //jssdk
