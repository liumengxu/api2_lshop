

@extends('layout.bst')
@section('content')
    <form action="goodslist" method="get">
    {{csrf_field()}}
    <h1 align="center" style="color:red;">商品列表展示 </h1>
    <table class="table table-striped">
        <tr>
            <td>id</td>
            <td>name</td>
            <td>price</td>
            <td>num</td>
        </tr>
        @foreach($list as $v)
            <tr>
                <td>{{$v->goods_id}}</td>
                <td>{{$v->name}}</td>
                <td>{{$v->price}}</td>
                <td>{{$v->num}}</td>
            </tr>
        @endforeach
        <input type="text" name="name">
        <input type="submit" value="搜索">
    </table>​
    {!! $list->appends(['name'=>$name])->render()!!}​
    </form>
@endsection