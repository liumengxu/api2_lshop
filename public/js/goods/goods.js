$('#add_cart_btn').click(function(e){
    e.preventDefault();
    var num=$("#goods_num").val();
    var goods_id=$("#goods_id").val();
    $.ajax({
        headers:{
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url:"/cart/insert",
        type:"post",
        data:{num:num,goods_id:goods_id},
        dataType:"json",
        success:function success(data){
            if(data.error==301){
                window.location.href=data.url;
            }else{
                alert(data.msg);
                window.location.href="/cart";
            }
        }
    })
})
