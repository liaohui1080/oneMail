<?php
//公共方法库
namespace LH;


//验证库
class Verif
{
    //判断字符串长度
    public static function strLength($min, $max, $str)
    {

        //去掉字符串首尾空格
        $str = trim($str);
        //字符串长度
        $strLength = strlen($str);

        if ($strLength >= $min && $strLength <= $max) {
            return true;
        } else {
            return false;
        }
    }


    //email格式验证
    public static function mail($str)
    {
        if ($str) {
            if (preg_match("/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/", $str)) {
                return true;
            } else {
                return false;
            }

        }
    }


    //验证密码是否符合长度
    public static function pass($str)
    {
        if ($str) {

            //密码只有数字和字母组成,
            if (preg_match("/[A-Za-z0-9][A-Za-z0-9]{5,15}/", $str)) {

                if (self::strLength(6, 16, $str)) {
                    return true;
                } else {
                    return false;
                }

            } else {
                return false;
            }

        }
    }

    //清除数组所有字符串元素两边的空格
    public static function trimArray($array)
    {
        $trimArray = [];
        foreach ($array AS $name => $value) {
            $trimArray[$name] = trim($value);
        }
        return $trimArray;
    }

    //登录公共函数-所有登录都调用这个函数
    /*函数详解
     * Vlogin($userMail,$userPass)
     *
     * ////////////////////////////////////////////////////////////////////////////////////////
     * 调用的其他函数
     * dbSave   		数据写入函数
     * dbfindOne     数据验证函数
     * trimArray                去掉数组字串两端空格函数
     * */
    public static function signIn($userMail, $userPass)
    {
        $array['userMail'] = $userMail;
        $array['userPass'] = $userPass;

        $array = self::trimArray($array);

        //检测mail
        if (!self::mail($array['userMail'])) {
            return array('zhuangtai' => false, 'tishi' => 'Email格式不对');
        } else if (!self::pass($array['userPass'])) {
            return array('zhuangtai' => false, 'tishi' => '密码不能小于6位大于16位');
        }

        //查找用户名是否存在
        $rs = Db::dbfindOne('User', array(
            'email' => $array['userMail'],
            'pass'  => $array['userPass']
        ));
        //dump($rs_user);

        if (!$rs) {

            return array('zhuangtai' => false, 'tishi' => '账号或者密码不正确');

        } else {//把登录成功的 user_mail  和 user_id 写入session

            Qita::setSessionCookie($rs['id']);
            Log::signInOut(1, $rs['id']);

            return array('zhuangtai' => true, 'tishi' => '登录成功');

        }


    }


    /*//////////////////////////////////////////////////////////////////////////////////////
     * 注册公共函数
     * 参数详解
     * array("user_mail"=>"注册mail","user_name"=>'注册用户名',"user_mima"=>'注册密码');
     ////////////////////////////////////////////////////////////////////////////////////////
     * 调用的其他函数
     * dbSave   		数据写入函数
     * dbfindOne     数据验证函数
     * trimArray                去掉数组字串两端空格函数
     */
    public static function signUp($userMail, $userPass)
    {
        $array['userMail'] = $userMail;
        $array['userPass'] = $userPass;
        $array = Verif::trimArray($array);
        //检测用户输入数据是否符合数据格式   ,检测函数以后再补上


        //检测mail
        if (!Verif::mail($array['userMail'])) {
            return array('zhuangtai' => false, 'tishi' => 'Email格式不对');
        } else if (!Verif::pass($array['userPass'])) {
            return array('zhuangtai' => false, 'tishi' => '密码不能小于6位大于16位');
        }

        //验证改email是否已经注册过
        $rs = Db::dbfindOne('User', array('email' => $array['userMail']));


        if ($rs) {
            //echo "email已存在\n";
            return array('zhuangtai' => false, 'tishi' => 'email已存在');
        } else {
            /*写入注册新用户
             * 在 user 表里 写入 mail 和 pass
             * 读取 user 表里 mail 字段对应的 user_id 用于下面写入别的表里使用
             * 在user_name 表里 写入 un_name  user_id  un_time
             * 在user_mima 表里写入 user_id , um_mima  um_time
             * 在 user_niming 表里写入 user_id  unm_time
             * */

            //在 user 表里 写入 mail
            $findUserID = Db::dbSave('User', array(
                'email'    => $array['userMail'],
                'pass'     => md5($array['userPass']),
                'time_int' => time()
            ));

            Qita::setSessionCookie($findUserID);
            Log::signInOut(1, $findUserID);
            return array('zhuangtai' => true, 'tishi' => '注册完成');

        }

    }

}


//数据库操作
class Db
{
    /**
     * 验证数据是否存在函数。只返回一条数据  find
     * 参数详解
     * @access $table = 表名字
     * @access $array_data=array("字段名"=>'字段数据'); 要查询的数据，可以多写几个
     * @access $array_paixv=array("排序字段",'排序规则');  array('user_id','desc')默认为空，如果需要查询，则在调用的时候使用这个数组
     * @return Array
     * ////////////////////////////////////////////////////////////////////////////////////////
     *
     * @global 调用的其他函数
     * trim_array                去掉数组字串两端空格函数
     *
     */
    public static function dbfindOne($table_name, $array_data, $array_paixv = NULL)
    {

        $array_data = Verif::trimArray($array_data);
        $table = M($table_name);
        if ($array_paixv) {//如果排序存在，就排序
            $ziduan = $array_paixv[0];
            $guize = $array_paixv[1];
            $rs_table = $table->where($array_data)->cache(true)->order($ziduan . ' ' . $guize)->find();
            //dump($rs_table);
        } else {//排序不存在
            $rs_table = $table->where($array_data)->cache(true)->find();
        }

        if ($rs_table) {
            return $rs_table;
        }
    }


    /**
     * @author写入数据
     * @author参数详解
     * @author$table_name=表名字
     * @author$array_data("字段名"=>"要写入的数据"); 多个字段写入，按数组方式添加
     * @author reurn  false true
     * */
    public static function dbSave($table_name, $array_data)
    {
        $table = D($table_name);
        $zhuangtai = $table->add($array_data);
        if ($zhuangtai) {
            return $zhuangtai;
        } else {
            return FALSE;
        }

    }


}

/*日志记录
 * Log
 * */

class Log
{
    /*登陆退出记录
 * signInUP($zhuangtai,$userID); $zhuangtai=参数 1 为登陆 2 为退出
 * */
    public static function signInOut($zhuangtai, $userID)
    {
        //把用户登录的时间 和ip 写入数据库
        $ip = get_client_ip();//获取ip
        $ip = intval(ip2long($ip)); //将ip地址转换为整数
        Db::dbSave("LoginOut",
            array(
                'user_id'   => $userID,
                'ip'        => $ip,
                'zhuangtai' => 1,
                'time_int'  => time()
            ));
    }
}

/*
 * 其他杂项
 * */

class Qita
{
    /*给session 和 cookie 赋值
     * setSessionCookie($userMail,$userID )
     * */
    public static function setSessionCookie($userID)
    {
        //session('userMail', $userMail);
        session('userID', $userID);

        //cookie('userMail', $userMail, 3600);
        cookie('userID', $userID, 3600);

    }

}

//权限判断
class Quanxian
{


    private static $congzhiqi = CONTROLLER_NAME; //当前控制器名
    private static $dongzuo = ACTION_NAME; //当前操作名

    //定义权限数组
    private static $quanxianArray = [
        "userGrop" => [

        ]
    ];

    //用户权限组
    private static $userGrop = [
        "guest"=>["index","sigIn","sigUp"],
        "signIn"=>[],
        "admin"=>[]
    ];

    function __construct()
    {

        //self::$dongzuo="ddd";
    }

    public static function init()
    {
        //new Quanxian();
        dump( self::$userGrop);

        echo self::$congzhiqi;
        echo self::$dongzuo;

    }
}


