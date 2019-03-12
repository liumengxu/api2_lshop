@extends('layout.bst')

@section('title', 'Page Title')
<form action="/weichat/formshow" method="post" enctype="multipart/form-data">
    {{csrf_field()}}
    表单测试
    <br>
    <input type="file" name="media">
    <input type="submit" value="上传">

</form>