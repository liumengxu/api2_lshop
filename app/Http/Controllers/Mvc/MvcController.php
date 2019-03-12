<?php
    namespace App\Http\Controllers\Mvc;

    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    class MvcController extends Controller{
        public function test(){
           return view("mvc.ma");
        }
    }

