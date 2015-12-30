<?php
//公共方法库
namespace LH;

use Think\Controller;
use Think\Model;//在这里加一个这就可以了(我是菜鸟)
use Org\Net\IpLocation;

//验证库
class Verif
{

    //判断中文汉字的长度
    public static function znStrLength($str, $min, $max = 999999)
    {
        $length = mb_strwidth($str);
        if ($length >= $min && $length <= $max) {
            return true;
        } else {
            return false;
        }
    }


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


    //信件字数验证
    public static function  mailNeirong($str, $intName)
    {
        if (!self::znStrLength($str, 300)) {
            exit (json_encode([
                'zhuangtai' => 0,
                'tishi'     => '信件内容不能少于300个汉字的',
                'data'      => '参数' . $intName . '=' . $str . '; 不能少于300个汉字 ,也不能大于999999个汉字'
            ]));
        }

    }

    //整数数字验证
    public static function  int($int, $intName)
    {

        //如果参数存在则验证格式
        if ($int) {

            //如果是数字,则验证数字是否符合正则标准
            if (is_numeric($int)) {
                if (!preg_match('/[0-9]*/', $int)) {
                    exit (json_encode([
                        'zhuangtai' => 0,
                        'tishi'     => '参数异常',
                        'data'      => '参数' . $intName . '=' . $int . '; 不是有效整数'
                    ]));
                }
            } else {
                exit (json_encode([
                    'zhuangtai' => 0,
                    'tishi'     => '参数异常',
                    'data'      => '参数' . $intName . '=' . $int . '; 不是有效整数'
                ]));
            }
        }


    }

    //参数格式检查
    public static function canshu($str, $strName = null, $bixv = false, $geshi = null)
    {

        //如果bixv=true 说明这是必填字段
        if ($bixv == true) {

            if (!$str) {
                exit (json_encode(['zhuangtai' => 0, 'tishi' => '参数' . $strName . '=' . $str . '; 不能为空']));
            }

            //格式存在就调用本类里面的验证方法
            if ($geshi) {
                //调用回调函数写法
                call_user_func_array(['self', $geshi], [$str, $strName]);
            }

        } else {
            //不是必须的参数,但是只要传过来, 就验证格式
            //格式存在就调用本类里面的验证方法
            if ($geshi) {
                //调用回调函数写法
                call_user_func_array(['self', $geshi], [$str, $strName]);
            }
        }

        //如果上面都运行通过了, 就返回这个参数的值
        return $str;
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

    //验证密码是否符合长度, 用于回调函数的使用
    public static function pass2($str, $strName)
    {
        if ($str) {


            if (self::strLength(6, 16, $str)) {

                //密码只有数字和字母组成,
                if (preg_match("/[A-Za-z0-9][A-Za-z0-9]{5,15}/", $str)) {
                    return $str;
                } else {
                    exit (json_encode([
                        'zhuangtai' => 0,
                        'tishi'     => '密码只能使用数字和字母',
                        'data'      => '参数' . $strName . '=' . $str . ';密码只能使用数字和字母'
                    ]));
                }


            } else {
                exit (json_encode([
                    'zhuangtai' => 0,
                    'tishi'     => '密码不能小于6位大于16位',
                    'data'      => '参数' . $strName . '=' . $str . ';密码不能小于6位大于16位'
                ]));
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


    //验证是否给自己谢谢
    public static function ziji($userID, $toUserID)
    {
        $userID = Verif::canshu($userID, 'userID', true, 'int');
        $toUserID = Verif::canshu($toUserID, 'toUserID', true, 'int');
        if ($userID == $toUserID) {
            exit (json_encode(['zhuangtai' => 0, 'tishi' => '不能给自己写信']));
        }

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
            'pass'  => md5($array['userPass'])
        ));
        //dump($rs_user);

        if (!$rs) {

            return array('zhuangtai' => false, 'tishi' => '账号或者密码不正确');

        } else {//把登录成功的 user_mail  和 user_id 写入session

            Qita::setSessionCookie($rs['id']);

            //获取用户信息,并存储到 cookie
            $userData = Qita::findUser($rs['id']);
            cookie('userData', json_encode($userData), 604800);

            Log::signInOut(1, $rs['id']); //写入登陆日志
            return array('zhuangtai' => true, 'tishi' => '登录成功', 'data' => $userData);

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

            //在 user 表里 写入 mail
            $findUserID = Db::dbSave('User', array(
                'email'    => $array['userMail'],
                'pass'     => md5($array['userPass']),
                'time_int' => time()
            ));

            Qita::setSessionCookie($findUserID);
            Log::signInOut(1, $findUserID);
            //获取用户信息
            $userData = Qita::findUser($findUserID);
            cookie('userData', json_encode($userData), 604800);
            return array('zhuangtai' => true, 'tishi' => '注册完成', 'data' => $userData);

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
            $rs_table = $table->where($array_data)->cache(false)->order($ziduan . ' ' . $guize)->find();
            //dump($rs_table);

        } else {//排序不存在
            $rs_table = $table->where($array_data)->cache(false)->find();
        }

        if ($rs_table) {
            return $rs_table;
        }
    }



    //获取所有数据
    //$array_paixv=array("排序字段",'排序规则');  array('user_id','desc')默认为空，如果需要查询，则在调用的时候使用这个数组
    public static function dbSelect($table_name, $array_data = null, $limit = null, $array_paixv = NULL)
    {

        $array_data = Verif::trimArray($array_data);
        $table = M($table_name);
        if ($array_paixv) {//如果排序存在，就排序

            $ziduan = $array_paixv[0];
            $guize = $array_paixv[1];

            if ($limit) {
                $rs_table = $table->where($array_data)->cache(false)->order($ziduan . ' ' . $guize)->limit($limit[0], $limit[1])->select();
            } else {
                $rs_table = $table->where($array_data)->cache(false)->order($ziduan . ' ' . $guize)->select();
            }

            //dump($rs_table);

        } else {//排序不存在

            if ($limit) {
                $rs_table = $table->where($array_data)->cache(false)->limit($limit[0], $limit[1])->select();
            } else {
                $rs_table = $table->where($array_data)->cache(false)->select();
            }

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
            echo $table_name . "表的数据创建失败";
            return FALSE;
        }

    }


    /**
     * @author更新数据
     * @author参数详解
     * @author$table_name=表名字
     * @author$array_data("字段名"=>"要写入的数据"); 多个字段写入，按数组方式添加
     * @author reurn  false true
     * */
    public static function dbUp($table_name, $array_data)
    {
        $table = D($table_name);
        $zhuangtai = $table->save($array_data);
        if ($zhuangtai) {
            return $zhuangtai;
        } else {
            echo $table_name . "表的数据更新失败";
            return "表的数据更新失败";
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
                'zhuangtai' => $zhuangtai,
                'time_int'  => time()
            ));
    }
}

/*
 * 其他杂项
 * */

class Qita
{
    /*设置session 和 cookie 赋值
     * setSessionCookie($userMail,$userID )
     * */
    public static function setSessionCookie($userID)
    {

        session('userID', $userID);

        cookie('userID', $userID, 604800);

        return true;

    }


    //从session 或者 cookie中获取userID的值
    public static function getSessionCookie()
    {
        $userID = null;
        if (session("userID")) {
            $userID = session("userID");
            return $userID;
        } else if (cookie('userID')) {
            $userID = cookie('userID');
            return $userID;
        } else {
            return false;
        }

    }

    //删除session 和 cookie的值
    public static function dropSessionCookie()
    {
        session("userID", null);

        cookie("userID", null);
        cookie("userData", null);
    }


    //获取用户的基本资料
    public static function findUser($userID)
    {

        $userID = Verif::canshu($userID, 'userID', true, 'int');

        $rs = Db::dbfindOne("User", ["id" => $userID]);
        unset($rs['pass']); //删除密码字段

        //获取用户位置
        $weizhi = Ip::getDbWeizhi($userID);
        if ($weizhi) {
            $rs['weizhi'] = $weizhi['weizhi'];
        } else {
            $rs['weizhi'] = '来自太阳系第三行星';
        }


        if (!$rs['name']) {
            $rs['name'] = "来自星星的你" . $rs['time_int'];
        }

        $userImg = Qita::getUserImg($userID);
        if ($userImg) {
            $rs['image'] = $userImg['img_name'];
        } else {
            $rs['image'] = 'moren.png';
        }

        return $rs;
    }

    //获取自己和这个收信人的来往信件列表的 key, 如果没有说明不是好友
    //参数说明 $toUserID=是收信人的id ,  $userID = 现在登陆用户的id
    public static function getUserListKey($userID, $toUserID)
    {

        $userID = Verif::canshu($userID, 'userID', true, 'int');
        $toUserID = Verif::canshu($toUserID, 'toUserID', true, 'int');

        //查询自己的 好友列表里是否有这个人,
        //同时也查询对方的好友列表里是否有自己,
        //如果都有的话则说明互相都是好友,然后返回mail_key字段
        $UserList = new Model();
        $UserListsql = "SELECT *
                  FROM mail_user_list
                  WHERE
                  (user_id= " . $userID . " AND shouxin_user_id = " . $toUserID . ")
                  OR
                  (user_id= " . $toUserID . " AND shouxin_user_id = " . $userID . ")";

        //获取第一行数据的 用户id 的key ,如果这个值存在说明互相已经是好友了
        $UserListKey = $UserList->query($UserListsql)[0]['mail_key'];

        //如果都有的话则说明互相都是好友,然后返回mail_key字段
        if (!$UserListKey) {

            // 批量添加数据, 把收信人id写到我的好友列表, 同时也把我写到 收信人id的好友列表,完成互相添加好友的动作
            $getID = Db::dbSave("UserList", ["user_id" => session("userID"), "shouxin_user_id" => $toUserID]);
            $getID2 = Db::dbSave("UserList", ["user_id" => $toUserID, "shouxin_user_id" => session("userID")]);


            //修改刚刚添加的两条数据的 mail_key 和时间 , 这个mail_key将作为用户来往信件列表的 唯一key
            $User = M("UserList");
            $User->save(['id' => $getID, 'mail_key' => md5($getID), 'time_int' => time()]); // 根据条件保存修改的数据
            $User->save(['id' => $getID2, 'mail_key' => md5($getID), 'time_int' => time()]); // 根据条件保存修改的数据

            //加密key
            $UserListKey = md5($getID);


        }
        return $UserListKey;

    }


    //获取自己和这个收信人的来往信件列表的 key, 如果没有就自动新建一个
    //参数说明 $toUserID ,是收信人的id  . $addKey默认=true 直接创建新key, =false 则不创建新key
    public static function getUserListKey备用($toUserID, $addKey = true)
    {

        //查询自己的 好友列表里是否有这个人,
        //同时也查询对方的好友列表里是否有自己,
        //如果都有的话则说明互相都是好友,然后返回mail_key字段
        $UserList = new Model();
        $UserListsql = "SELECT *
                  FROM mail_user_list
                  WHERE
                  (user_id= " . session('userID') . " AND shouxin_user_id = " . $toUserID . ")
                  OR
                  (user_id= " . $toUserID . " AND shouxin_user_id = " . session('userID') . ")";
        $UserListKey = $UserList->query($UserListsql)[0]['id'];

        if ($UserListKey) {
            $UserListKey = md5($UserListKey);
        } else {

            //如果 $addKey 参数存在,则创建新的key,否则,只返回这次查询的的数据
            if ($addKey) {
                // 批量添加数据, 把收信人id写到我的好友列表, 同时也把我写到 收信人id的好友列表,完成互相添加好友的动作
                $getID = Db::dbSave("UserList", ["user_id" => session("userID"), "shouxin_user_id" => $toUserID]);
                $getID2 = Db::dbSave("UserList", ["user_id" => $toUserID, "shouxin_user_id" => session("userID")]);


                //修改刚刚添加的两条数据的 mail_key 和时间 , 这个mail_key将作为用户来往信件列表的 唯一key
                $User = M("UserList");
                $User->save(['id' => $getID, 'mail_key' => md5($getID), 'time_int' => time()]); // 根据条件保存修改的数据
                $User->save(['id' => $getID2, 'mail_key' => md5($getID), 'time_int' => time()]); // 根据条件保存修改的数据

                //加密key
                $UserListKey = md5($getID);
            }
        }

        return $UserListKey;
    }


    //获取邮件列表, 根据user_list表用户互相加好友以后得到的key在获取邮件
    public static function getUserMailList($userListKey, $page)
    {

        $userListKey = Verif::canshu($userListKey, "userListKey", true);
        //返回所有信件内容
//        $mailAll = Db::dbSelect("UserMailList", ['mail_key' => $userListKey], null, ['id', 'desc']);

        $page = Verif::canshu($page, "page", true, 'int');//验证

        // $da['id']=109;

        $config = array(
            'tablename' => 'UserMailList', // 表名
            'order'     => 'id desc', // 排序
            'where'     => ['mail_key' => $userListKey],
            'page'      => $page,  // 页码，默认为首页
            'num'       => 10  // 每页条数
        );
        $pageData = new Page($config);
        $mail = $pageData->get();

        $mailAll = $mail['data'];
        //dump($mailAll);

//        if(intval($page)>$mailAll['tongji']['count_page']){
//            $this->ajaxReturn(["zhuangtai" => 0, "tishi" => "没有了" , "data" => $mailAll]);
//        }else{
//            $mailAll['data']['user']=Qita::findUser($mailAll['data']['user_id']);
//
//            $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "公开信获取成功", "data" => $mailAll]);
//        }


        foreach ($mailAll as $key => $val) {

            //获取发信人信息 ,把用户资料加到新的字段里
            $mailAll[$key]['faxin_user'] = self::findUser($mailAll[$key]['faxin_user_id']);
            $mailAll[$key]['shouxin_user'] = self::findUser($mailAll[$key]['shouxin_user_id']);
            //dump($mailAll);

            //如果来自公开邮件, 就获取这封公开邮件
            if ($mailAll[$key]['open_mail_id']) {

                //获取公开信内容
                $openMail = Db::dbfindOne("OpenMail", ['id' => $mailAll[$key]['open_mail_id']]);
                //获取公开信的发信人信息
                $openMail['user'] = self::findUser($openMail['user_id']);

                $mailAll[$key]['open_mail'] = $openMail;
            }
        }

        //获取收信人信息,从取的来往信件列表中获取,
        // 如果登陆用户id 和收信人id一样, 则取发信人信息
        //如果登陆用户id 和发信人一样, 则取收信人信息,
        if (session("userID") == $mailAll[0]['shouxin_user_id']) {
            $shouxinUser = $mailAll[0]['faxin_user'];
        } else if (session("userID") == $mailAll[0]['faxin_user_id']) {
            $shouxinUser = $mailAll[0]['shouxin_user'];
        }

        return ['tongji' => $mail['tongji'], 'data' => $mailAll, 'shouxin_user' => $shouxinUser];
    }


    //获取邮件列表, 根据user_list表用户互相加好友以后得到的key在获取邮件
    public static function getUserMailList备用($userListKey)
    {

        $userListKey = Verif::canshu($userListKey, "userListKey", true);
        //返回所有信件内容
        $mailAll = Db::dbSelect("UserMailList", ['mail_key' => $userListKey], null, ['id', 'desc']);


        foreach ($mailAll as $key => $val) {

            //获取发信人信息 ,把用户资料加到新的字段里
            $mailAll[$key]['faxin_user'] = self::findUser($mailAll[$key]['faxin_user_id']);
            $mailAll[$key]['shouxin_user'] = self::findUser($mailAll[$key]['shouxin_user_id']);
            //dump($mailAll);

            //如果来自公开邮件, 就获取这封公开邮件
            if ($mailAll[$key]['open_mail_id']) {

                //获取公开信内容
                $openMail = Db::dbfindOne("OpenMail", ['id' => $mailAll[$key]['open_mail_id']]);
                //获取公开信的发信人信息
                $openMail['user'] = self::findUser($openMail['user_id']);

                $mailAll[$key]['open_mail'] = $openMail;
            }
        }

        //获取收信人信息,从取的来往信件列表中获取,
        // 如果登陆用户id 和收信人id一样, 则取发信人信息
        //如果登陆用户id 和发信人一样, 则取收信人信息,
        if (session("userID") == $mailAll[0]['shouxin_user_id']) {
            $shouxinUser = $mailAll[0]['faxin_user'];
        } else if (session("userID") == $mailAll[0]['faxin_user_id']) {
            $shouxinUser = $mailAll[0]['shouxin_user'];
        }

        return ['count' => count($mailAll), 'data' => $mailAll, 'shouxin_user' => $shouxinUser];
    }


    //用户的好友列表
    public static function getFriendList($userID, $limit = null)
    {
        Verif::canshu($userID, "userID", true, 'int');//验证
        $list = Db::dbSelect("UserList", ['user_id' => $userID]);
        foreach ($list as $key => $listVal) {

            $list[$key]['shouxin_user'] = Qita::findUser($listVal['shouxin_user_id']);

        }
        //dump($list);
        return $list;
    }


    //获取用户头像
    public static function getUserImg($userID)
    {

        $data = Db::dbfindOne("UserImg", ['user_id' => $userID], ['id', 'desc']);
        return $data;

    }

}//end Qita

//权限判断
class Quanxian
{


    private static $kongzhiqi = CONTROLLER_NAME; //当前控制器名
    private static $dongzuo = ACTION_NAME; //当前操作名


    /*用户权限组
     *用户分组说明
     * 没登陆用户=youke 算是游客
     * 只登陆用户算是会员 = huiyuan
     * 登陆并且通过email验证的用户= mailHuiyuan
     * 高级用户 = VIPhuiyuan
     * 管理员 = admin
     *
     * 操作后面的参数说明
     *  ["jiangeTime" => 间隔时间 ,tishi=> 如果这个提示信息存在, 就用这个否则用默认的]
     * */
    private static $userGrop = [

        "youke"        => ["游客", [
            "index"    => ["jiangeTime" => 3],
            "indexOpenMail"=> ["jiangeTime" => 3],
            "sigIn"    => ["jiangeTime" => 0],
            "sigUp"    => ["jiangeTime" => 0],
            "openMail" => ["jiangeTime" => 0],
            "getIP" => ["jiangeTime" => 0],
        ]],

        "huiyuan"      => ["会员", [
            "index"=> ["jiangeTime" => 3],
            "indexOpenMail"=> ["jiangeTime" => 3],
            "userIndex"=> ["jiangeTime" => 0],
            "sigOut"=> ["jiangeTime" => 0],
            "addMailAll"=> ["jiangeTime" =>  60,"tishi"=>'等下一分钟再写公开信把'],
            "openMail"=> ["jiangeTime" => 0],
            "userMailList"=> ["jiangeTime" => 0],
            "addMailOne"=> ["jiangeTime" => 5,"tishi"=>'一小时之内只能写一封信的'],
            'friendList'=> ["jiangeTime" => 0],
            'userMailWeidu'=> ["jiangeTime" => 0],
            'getuserMailOne'=> ["jiangeTime" => 0],
            'userPassUp'=> ["jiangeTime" => 0],
            'userNameUp'=> ["jiangeTime" => 0],
            "userAll"=> ["jiangeTime" => 0],
            "setWeizhi"=> ["jiangeTime" => 0],
            "getIP" => ["jiangeTime" => 0],
            "upUserImg"=> ["jiangeTime" => 0]
        ]],
        "emailHuiyuan" => ["Email验证会员", [

            "index"=> ["jiangeTime" => 3],
            "indexOpenMail"=> ["jiangeTime" => 3],
            "userIndex"=> ["jiangeTime" => 0],
            "sigOut"=> ["jiangeTime" => 0],
            "addMailAll"=> ["jiangeTime" => 60,"tishi"=>'等下一分钟再写公开信把'],
            "openMail"=> ["jiangeTime" => 0],
            "userMailList"=> ["jiangeTime" => 0],
            "addMailOne"=> ["jiangeTime" => 5,"tishi"=>'一小时之内只能写一封信的'],
            'friendList'=> ["jiangeTime" => 0],
            'userMailWeidu'=> ["jiangeTime" => 0],
            'getuserMailOne'=> ["jiangeTime" => 0],
            'userPassUp'=> ["jiangeTime" => 0],
            'userNameUp'=> ["jiangeTime" => 3],
            "userAll"=> ["jiangeTime" => 0],
            "setWeizhi"=> ["jiangeTime" => 0],
            "getUserData"=> ["jiangeTime" => 0],
            "getIP" => ["jiangeTime" => 0],
            "upUserImg"=> ["jiangeTime" => 0]

        ]],
        "VIPhuiyuan"   => ["VIP会员", ["userIndex", "sigOut"]],
        "admin"        => ["管理员", ["userIndex", "sigIn", "sigUp"]]
    ];


    function __construct()
    {
        //子类想用父类的 构造方法,需要重新 new父类 才可以调用
        //echo "这是权限的构造方法";

    }


    /*
     * 获取用户所在分组
   */
    public static function getUserGrop()
    {
        //游客
        if (!Qita::getSessionCookie()) {
            return self::$userGrop['youke'];
        }

        //会员 或者 email会员
        if (Qita::getSessionCookie()) {
            $userID = Qita::getSessionCookie();

            //检测email是否通过验证,只获取最新的一条
            $rs = Db::dbfindOne("EmailYanzheng", ["user_id" => $userID], ['time_int', 'desc']);

            if ($rs) {
                //email会员
                return self::$userGrop['emailHuiyuan'];
            } else {

                //普通会员
                return self::$userGrop['huiyuan'];
            }

        }

        //验证 VIP会员的条件 以后补上

        //验证 管理员的条件以后补上


    }


    //初始化方法 $canshu 是接收到网页传过来的变量 ,主要用于 判定间隔时间的session的名字
    public static function init($canshu=null)
    {
        //echo  __MODULE__;
        //当前操作名
        $dongzuo = self::$dongzuo;



        //获取当前用户能使用的动作
        $userDongzuo = self::getUserGrop();

        //获取用户分组名称
        $userGropName=$userDongzuo[0];

        //检测数组中的key是否有这个操作名, 返回布尔值
        $actionKey = array_key_exists($dongzuo, $userDongzuo[1]);

        //获取当前操作的提交数据的间隔时间
        $actionJiangeTime=$userDongzuo[1][$dongzuo]['jiangeTime'];

        //获取当前操作的提交数据的间提示信息
        $actionJiangeTishi=$userDongzuo[1][$dongzuo]['tishi'];
        //dump($actionJiangeTishi);

        //给当前动作加一个前缀,用户session存储
        $dongzuoSession="dz_".$dongzuo.$canshu;

       // echo $dongzuoSession;
        //检测数组中是否有这个操作名
        if (!$actionKey) {
            // 返回JSON数据格式到客户端 包含状态信息
            exit(json_encode(["quanxian" => 1, "tishi" => "你无权访问" ,'data'=>$userGropName]));
        }else{

            //如果间隔时间大于0 ,则说明访问这个操作的时候,需要现在用户的访问时间
            if($actionJiangeTime>0){
                //检测这个操作上传访问的时间
                if(session($dongzuoSession)){
                    //echo  $dongzuoSession;
                    //用现在时间减去 session时间,来判定用户间隔了多少秒访问的
                    $jiange=time()-session($dongzuoSession);

                    //间隔时间小于 操作设置的间隔时间则抛出错误
                    if($jiange<$actionJiangeTime){

                        if($actionJiangeTishi){
                            $tishi=$actionJiangeTishi;
                        }else{
                            $tishi='你提交的太频繁的';
                        }
                        exit(json_encode(["quanxian" => 1, "tishi" => $tishi,'data'=>$jiange]));
                    }else{

                        //如果大于间隔时间,这重新赋值session
                        session($dongzuoSession,time());
                    }

                }else{
                    //如果session不存在,则说明是第一来访问,直接添加session时间
                    session($dongzuoSession,time());
                };
            }

        }


    }
}


class Page
{
    protected $config = array(
        'tablename' => 'Post', // 表名
        'where'     => '', // 查询条件
        'relation'  => '', // 关联条件
        'order'     => 'id desc', // 排序
        'page'      => 1,  // 页码，默认为首页
        'num'       => 5  // 每页条数
    );

    function __construct($config = array())
    {
        $config['tablename'] = ucfirst($config['tablename']);

        // 合并配置文件
        $this->config = array_merge($this->config, $config);
    }


    //每页可以获取多条数据
    public function get()
    {
        // 实例化数据库
        $dbIns = D($this->config['tablename']);

        // 获取查询条件
        $map = array();
        if (!empty($this->config['where'])) {
            $map = $this->config['where'];
        }

        // 统计表中条数
        $count = $dbIns->where($map)->count();

        // 查询条件拼装
        $condition = $dbIns
        ->where($map)
        ->order($this->config['order'])
        ->limit(($this->config['page'] - 1) * $this->config['num'], $this->config['num']);

        // 查询数据
        $relation = array();
        if (!empty($this->config['relation'])) {
            // 关联模型查询数据
            $relation = $this->config['relation'];
            $data = $condition->relation($relation)->select();
        } else {
            // 正常查询数据、视图模型查询数据
            $data = $condition->select();
        }


        // 返回当前页和总页数
        $tongji['now_page'] = intval($this->config['page']);  //当前第几页
        $tongji['count_page'] = intval(ceil($count / $this->config['num'])); //页数
        $tongji['count_data'] = intval($count);  //有多少条数据

        return ['data' => $data, 'tongji' => $tongji];
    }


    //每页只获取一条数据
    public function getOne()
    {
        // 实例化数据库
        $dbIns = D($this->config['tablename']);

        // 获取查询条件
        $map = array();
        if (!empty($this->config['where'])) {
            $map = $this->config['where'];
        }

        // 统计表中条数
        $count = $dbIns->where($map)->count();

        // 查询条件拼装
        $condition = $dbIns
        ->where($map)
        ->order($this->config['order'])
        ->limit(($this->config['page'] - 1) * $this->config['num'], $this->config['num']);

        // 查询数据
        $relation = array();
        if (!empty($this->config['relation'])) {
            // 关联模型查询数据
            $relation = $this->config['relation'];
            $data = $condition->relation($relation)->select();
        } else {
            // 正常查询数据、视图模型查询数据
            $data = $condition->select();
        }


        // 返回当前页和总页数
        $tongji['now_page'] = intval($this->config['page']);  //当前第几页
        $tongji['count_page'] = intval(ceil($count / $this->config['num'])); //页数
        $tongji['count_data'] = intval($count);  //有多少条数据

        return ['data' => $data[0], 'tongji' => $tongji];
    }
}

class Ip
{

    /*
     * 从淘宝ip库获得ip的位置信息 .从国外无法访问
     * @public $clientIP
     * */
    private static function taobaoIP($ip)
    {

        $taobaoIP = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
        $IPinfo = json_decode(file_get_contents($taobaoIP));
        return $IPinfo->data;
    }


    //从纯真ip数据库获取ip位置
    private static function chunzhenIP($ip){
        $ip = Verif::canshu($ip, 'chunzhenIP$ip', true);
        $Ips = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
        $IPinfo = $Ips->getlocation($ip); // 获取某个IP地址所在
        return $IPinfo;

    }



    //将位置写入数据库
    public static function  setWeizhi($ip, $weizhi)
    {
        $ip = Verif::canshu($ip, 'setWeizhi$ip', true);
        $weizhi = Verif::canshu($weizhi, '$weizhi', true);

        $data = Db::dbSave("UserWeizhi", ['user_id' => session("userID"), 'ip' => $ip, 'weizhi' => $weizhi, 'time_int' => time()]);

        if ($data) {
            return true;
        } else {
            return false;
        }

    }

    //获取ip的真实地址,并写入数据库
    public static function getWeizhi($ip)
    {

//        $ip = get_client_ip();//获取ip
        //$ip = self::get_onlineip();//获取ip

        if ($ip) {

            $data = self::chunzhenIP($ip); //获取位置数据
            $dataIP = $data['ip']; //获取ip地址
            $dataFrom = $data['country']; //获取城市
            //Dump($data);
            //写入数据库
            self::setWeizhi($dataIP, $dataFrom);


            return ["zhuangtai" => 1, 'tishi' => '定位成功', 'data' => ['ip' => $ip, 'weizhi' => $dataFrom]];
        } else {
            return false;
        }

    }



    //从数据库里获取用户的位置
    public static function getDbWeizhi($userID)
    {
        $weizhi = Db::dbfindOne("UserWeizhi", ['user_id' => $userID], ['id', 'desc']);
        return $weizhi;
    }

}

//文件操作类
class File
{


    public static function userImg()
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Public/'; //设置上传主目录
        $upload->savePath = './userImg/'; // 设置附件上传目录
        $upload->autoSub = false;
        // 上传文件
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            //dump($upload->getError());
            exit(json_encode(["zhuangtai" => 0, "tishi" => "上传错误", 'data' => $upload]));

        } else {// 上传成功

//            dump($info);
            $userImg = $info['file']['savename'];
//            echo $userImg;
            //写入用户头像数据库
            Db::dbSave("UserImg", ['user_id' => session('userID'), 'img_name' => $userImg, 'time_int' => time()]);

            //dump($userImg);
            echo(json_encode(["zhuangtai" => 1, "tishi" => "修改成功", 'data' => $userImg]));
//            return ["zhuangtai" => 1, "tishi" => "修改成功", 'data' => $userImg];
        }
    }
}


//统计类
class Count
{


    /* 统计数增加
     * $tableName =表名字
     * $where = 筛选字段数组
     * $upZiduan = 要更新的字段
     * $upInt = 每次更新的数字
     * */
    private static function countAdd($tableName, $where, $upZiduan, $upInt)
    {
        //增加 user_list 的mail_count数
        $count = M($tableName); // 实例化User对象
        $count->where($where)->setInc($upZiduan, $upInt); // 用户的积分加3
    }

    /* 统计数 减少
    * $tableName =表名字
     * $where = 筛选字段数组
     * $upZiduan = 要更新的字段
     * $upInt = 每次更新的数字
     * */
    private static function countJian($tableName, $where, $upZiduan, $upInt)
    {
        $count = M($tableName); // 实例化User对象
        $count->where($where)->setDec($upZiduan, $upInt); // 用户的积分加3
    }


    //给user_list 增加未读邮件数
    public static function newMailCountAdd($where)
    {
        self::countAdd("UserList", $where, 'new_mail_count', 1);
    }

    //给user_list 减少未读邮件数
    public static function newMailCountJian($where)
    {
        self::countJian("UserList", $where, 'new_mail_count', 1);
    }
}