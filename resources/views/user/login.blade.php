@extends('layout.ma')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>用户登录</title>
</head>
<body>
<h4 style="text-align:center">用户登录</h4>
<form action="userlogin" method="POST" class="form-horizontal">
    {{csrf_field()}}

    <div class="form-group">
        <label  class="col-sm-2 control-label">用户名</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="uname">
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">密码</label>
        <div class="col-sm-8">
            <input type="password" class="form-control" name="pwd">
        </div>
    </div>
    <label  class="col-sm-2 control-label"></label>
    <div class="col-sm-2">
        <button type="submit" class="btn btn-info" >登录</button>
        <a href="https://open.weixin.qq.com/connect/qrconnect?appid=wxe24f70961302b5a5&amp;redirect_uri=http%3a%2f%2fmall.77sc.com.cn%2fweixin.php%3fr1%3dhttp%3a%2f%2fxue.qianqianya.xyz%2fweichat%2fcode&amp;response_type=code&amp;scope=snsapi_login&amp;state=STATE#wechat_redirect"  class="btn btn-info">微信登录</a>
    </div>
</form>
</body>
</html>