<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource("/goods",GoodsController::class);
    $router->resource("/users",UsersController::class);
    $router->resource("/wxusers",WeixinController::class);  //微信用户列表
    $router->resource("/wxmedia",WxmediaController::class);  //微信临时素材列表

    $router->resource("/wxmateria",ForeverController::class);  //微信永久素材列表
    $router->post("/wxmateria",'ForeverController@fileshow');  //微信永久素材列表

    $router->get("/wxsend",'WxsendController@index');  //微信后台群发信息  页面
    $router->post("/wxsend",'WxsendController@textGroup');  //微信后台群发信息
});
