@extends('layout.bst')
@section('content')

    <h1 align="center" style="color:red;">Welcome back </h1>
    <a href="/order/add" style="align:right">提交订单</a>
    <a href="/cart/quit" style="align:right">||退出</a>
    <table class="table table-striped">
        <tr>
            <td>name</td>
            <td>num</td>
            <td>price</td>
            <td>c_time</td>
            <td>操作</td>
        </tr>
        @foreach($list as $v)
            <tr>
                <td>{{$v["name"]}}</td>
                <td>{{$v["num"]}}</td>
                <td>{{$v["price"] / 100}}</td>
                <td>{{date("Y-m-d H:i:s"),$v["c_time"]}}</td>
                <td>
                    <a href="/cart/del2/{{$v["c_id"]}}">删除</a>
                    <a href="/order/add">提交订单</a>
                </td>
            </tr>
        @endforeach
    </table>
@endsection