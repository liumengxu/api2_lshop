@extends('layout.ma')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>用户注册</title>
</head>
<body>

<form class="form-horizontal" action="userreg" method="POST">
    {{csrf_field()}}
    <h4 style="text-align:center">用户注册</h4>
    <div class="form-group">
        <label  class="col-sm-2 control-label">用户名</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="uname">
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">密码</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="pwd">
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">年龄</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="age">
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">邮箱</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" name="email">
        </div>
    </div>
    <label  class="col-sm-2 control-label"></label>
    <div class="col-sm-8">
        <button type="submit" class="btn btn-info" >注册</button>
    </div>
</form>
</body>
</html>