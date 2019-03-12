<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        '/test/*',
        '/pay/alipay/notify',
        '/weichat/valid',
        '/weichat/notice',  //异步通知回调
        '/weichat/success'
    ];
}
