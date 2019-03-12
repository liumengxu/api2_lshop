@extends('layout.bst')
@section('content')
    <h3 align="center">订单支付</h3>
    <div id="qrcode" align="center"></div>
    <input type="hidden" value="{{$code_url}}" id="code_url">
    <input type="hidden" value="{{$order_number}}" id="order_number">
@endsection

@section('footer')
    <script type="text/javascript" src="/js/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="/js/qrcode.min.js"></script>
    <script>
        setInterval(function () {
            var order_number=$("#order_number").val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url     :   '/weichat/success',
                type    :   'post',
                data    :   {order_number:order_number},
                success :   function(res){
                    if(res==1){
                        alert('支付成功');
                        //location.href="/weichat/success";
                    }
                }
            })
        },3000);
        var code_url=$('#code_url').val();
        //console.log(code_url);
        var qrcode = new QRCode('qrcode', {
            text:code_url,
            width: 100,
            height: 100,
            colorDark : '#000000',
            colorLight : '#ffffff',
            correctLevel : QRCode.CorrectLevel.H
        });
        // 使用 API
        qrcode.clear();
        qrcode.makeCode(code_url);
    </script>

@endsection