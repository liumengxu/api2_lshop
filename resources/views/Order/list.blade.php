@extends('layout.bst')
@section('content')

    <h1 align="center" style="color:red;">Welcome back </h1>
    <a href="/order/quit" style="align:right">退出</a>
    <table class="table table-striped">
        <tr>
            <td>订单号</td>
            <td>订单金额</td>
            <td>c_time</td>
            <td>操作</td>
        </tr>
        @foreach($orderInfo as $v)
            <tr>
                <td>{{$v["order_number"]}}</td>
                <td>{{$v["order_amount"]/100}}</td>
                <td>{{date("Y-m-d H:i:s"),$v["c_time"]}}</td>
                <td>
                    <a href="/order/del/{{$v["o_id"]}}">删除</a>
                    <a href="/order/detail/{{$v["order_number"]}}">订单详情</a>
                </td>
            </tr>
        @endforeach
    </table>
@endsection