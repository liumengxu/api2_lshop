<?php

namespace App\Http\Middleware;

use Closure;

class checkLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(empty(session()->get('id'))){
            echo "账户不存在";exit;
        }
//        if($_COOKIE['token']!=$request->session()->get('u_token')){
//            die("非法请求");
//        }else{
//            //echo "正常请求";
//        }
//        $request->session()->get("u_token");
//        echo "u_token".$request->session()->get("u_token");
        if(empty(session()->get('id'))){
            header("refresh:1,/userlogin");
            echo "请先登录";
        }
        return $next($request);
    }
}
