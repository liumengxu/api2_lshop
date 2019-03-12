@extends('layout.bst')
@section('content')

    <h1 align="center" style="color:red;">Welcome back </h1>
    <h5>操作：</h5><a href="/order/quit" style="align:right">退出</a>
    <a href="/order/del/{{$order_number}}/{{$status}}">||取消订单</a>
    <a href="/pay/alipay/test/{{$order_number}}">||支付宝支付</a>
    <a href="/weichat/order/{{$order_number}}">||微信支付</a>
    <table class="table table-striped">
        <tr>
            <td>订单号</td>
            <td>订单名称</td>
            <td>购买数量</td>
            <td>单价</td>
            <td>订单状态</td>
            <td>操作</td>
        </tr>
        @foreach($detailInfo as $v)
            <tr>
                <td>{{$v["order_number"]}}</td>
                <td>{{$v["name"]}}</td>
                <td>{{$v["buy_number"]}}</td>
                <td>{{$v["price"]}}</td>
                <td>@if($v["status"]==1)
                    待支付
                    @elseif($v["status"]==2)
                    已支付
                    @else($v["status"]==3)
                    已取消
                @endif</td>
                <td>
                    <a href="/pay/alipay/test/{{$order_number}}">支付宝支付</a>
                    <a href="/weichat/order/{{$order_number}}">微信支付</a>
                    {{--<a href="/pay/pay/{{$order_number}}/{{$status}}">去支付</a></td>--}}
            </tr>
        @endforeach
    </table>
@endsection