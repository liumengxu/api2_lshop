@extends('layout.bst')
@section('content')
    <form action="/typePdf" method="post" enctype="multipart/form-data">
        {{csrf_field()}}
        <h1 align="center" style="color:red;">Welcome back </h1>
        <a href="/order/quit" style="align:right">退出</a>
        <table class="table table-striped">
            <input type="file" name="type" value="请选择文件">
            <input type="submit" value="上传">
        </table>
    </form>
@endsection