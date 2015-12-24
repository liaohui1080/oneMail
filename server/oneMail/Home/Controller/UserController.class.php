<?php
namespace Home\Controller;

use Think\Controller;

use LH\Verif;
use LH\Db;
use LH\Quanxian;
use LH\Qita;
use LH\Log;

class UserController extends Controller
{
    //构造方法
    function __construct() {

        Quanxian::init();

    }
    public function userIndex()
    {
        echo "index页面";


    }



}