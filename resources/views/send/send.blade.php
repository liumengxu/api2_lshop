@extends('layout.bst')

@section('title', 'Page Title')
<form action="wxsend" method="post" >
    {{csrf_field()}}


    <input type="text" name="text">
    <input type="submit" value="发送">


</form>
@endsection