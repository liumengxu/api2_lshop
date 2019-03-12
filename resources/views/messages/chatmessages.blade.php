@extends('layout.bst')
@section('title', 'Page Title')

<form action="/weichat/massage" method="post" >
    {{csrf_field()}}}
    <div >
        <textarea style="border: solid red 1px " cols="80px" rows="30px"> </textarea>
    </div>
    <div >
        <input type="text"><input type="submit" value="发送">
    </div>
</form>
@endsection
