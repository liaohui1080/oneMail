<?php
namespace Home\Controller;


use LH\Count;
use LH\File;
use Think\Controller;

use LH\Verif;
use LH\Db;
use LH\Quanxian;
use LH\Qita;
use LH\Log;
use LH\Page;
use LH\Ip;
use Think\Model;


class IndexController extends Controller
{
    //构造方法
    function __construct()
    {
        //调用父类的构造方法
        parent::__construct();

        Quanxian::init(I('toUserID'));

    }

    public function index()
    {
        //echo "index页面";
       // dump(php_uname('s'));
        //Verif::signUp("liaohui1080@tianliaohui.com","123456");

        //$dd=Verif::signIn("liaohui1080@tianliaohui.com","123456");
        //dump($dd);
        //Qita::dropSessionCookie();

//        dump(Verif::strLength(1, 4, "dddddddd"));
//        dump(Verif::mail("liaohui@tianliaohui.com"));
//        dump(Verif::pass("liaohui#####"));
//        //strLength(1,4,"dddddddd");
//        $ajax["zhuangtai"] = 0;
//        $ajax["tishi"] = "ddafd";
//        $ajax["data"] = "dafsdfasd";
//        //$this->ajaxReturn($ajax);
////        $dd=Vlogin("ddddddd@tianlioahui.com","adfadsfasdfa");
//        $ip = get_client_ip();//获取ip
//        $ip = intval(ip2long($ip)); //将ip地址转换为整数
//        dump(ip2long($ip));
//        $fff=Verif::signUp("liaohui5@tianliaohui.cn", "liaohui1080!!!!");
//        dump($fff);


        //echo ACTION_NAME ;
//        $rs=M("User");
//        dump($rs->select());


//        $user=Qita::findUser(session("userID"));
//
//        $this->userName=$user['name'];
        $this->display();
    }

    //显示所有用户
    public function userAll()
    {
        $rsUser = Db::dbSelect("User");
        dump($rsUser);
    }




    //ip地址定位城市,并存入数据库
    public function  setWeizhi(){

        $ip = Verif::canshu(I('ip'), 'ip', true);
        $weizhi= Ip::getWeizhi($ip);

        $this->ajaxReturn($weizhi);
    }

    //获取ip
    public function getIP(){
        $callback = $_GET[callback ];
        $ip=get_client_ip();
        //echo $ip;
        echo $callback.'('.json_encode(['zhuangtai'=>1,'tishi'=>'获取ip成功','data'=>$ip]).')';

        //$this->ajaxReturn($dd);
    }


    //登陆操作
    public function sigIn()
    {
        $userMail = $_GET['userMail'];
        $userPass = $_GET['userPass'];
        $find = Verif::signIn($userMail, $userPass);

        $this->ajaxReturn($find);
    }

    //注册操作
    public function sigUp()
    {
        $userMail = $_GET['userMail'];
        $userPass = $_GET['userPass'];
        $find = Verif::signUp($userMail, $userPass);
        $this->ajaxReturn($find);
    }

    //退出
    public function sigOut()
    {

        Log::signInOut(2, session('userID')); //记录退出日志
        Qita::dropSessionCookie();
        $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "退出成功"]);
    }


    //修改密码
    public function userPassUp()
    {
//      $userID = Verif::canshu(I('userID'), '$userID', true, 'int');
        $userID = Verif::canshu(session('userID'), '$userID', true, 'int');
        $userPassOld = Verif::canshu(I('userPassOld'), 'userPass', true, 'pass2');
        $userPass = Verif::canshu(I('userPass'), 'userPass', true, 'pass2');

        //判断老密码是否正确
        $userPassYanzheng = Db::dbfindOne("User",['id'=>$userID, 'pass'=>md5($userPassOld)]);
        if($userPassYanzheng){

            //判断新密码是否和老密码相同
            if($userPassYanzheng['pass']==md5($userPass)){
                $this->ajaxReturn(["zhuangtai" => 0, "tishi" => "新密码不能和老密码相同"]);
            }else{
                Db::dbUp("User", ['id' => $userID, 'pass' => md5($userPass)]);
                $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "修改成功"]);
            }

        }else{
            $this->ajaxReturn(["zhuangtai" => 0, "tishi" => "老密码不正确"]);
        }



    }

    //修改名字
    public function userNameUp()
    {
        $userID = session("userID");
        $userName = Verif::canshu(I('userName'), 'userName', true);
        Db::dbUp("User", ['id' => $userID, 'name' => $userName]);
        $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "修改名字成功"]);
    }

    //修改用户照片
    public function upUserImg()
    {

          File::userImg();

    }



    //获取一封公开信
    public function openMail()
    {
        $page= Verif::canshu(I('page'), "page", false, 'int');//验证

       // $da['id']=109;

        $config = array(
            'tablename' => 'OpenMail', // 表名
            'order'     => 'id desc', // 排序
            'page'      => $page,  // 页码，默认为首页
            'num'       => 1  // 每页条数
        );
        $pageData = new Page($config);
        $data = $pageData->getOne();

        if(intval($page)>$data['tongji']['count_page']){
            $this->ajaxReturn(["zhuangtai" => 0, "tishi" => "没有了" , "data" => $data]);
        }else{
            $data['data']['user']=Qita::findUser($data['data']['user_id']);

            $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "公开信获取成功", "data" => $data]);
        }
        //dump($page);
    }



    //获取首页面显示的公开信
    public function indexOpenMail(){
        $id= Verif::canshu(I('indexID'), "id", true, 'int');//验证

        $data=Db::dbfindOne("OpenMail",['id'=>$id]);
        $data['user']=Qita::findUser($data['user_id']);
//        cookie("indexID",$data['id']);
        //Dump($data);
        $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "首页公开信获取成功", "data" => $data]);
    }


    //增加一封公开信,也就是写一封给所有人的信
    public function addMailAll()
    {
        //验证输入内容的格式和长度, 这还没有验证,等以后补上
        $mailNeirong = Verif::canshu(I('mailNeirong'), 'mailNeirong', true,'mailNeirong'); //邮件内容

        $rs = Db::dbSave("OpenMail",
            ["user_id"      => session("userID"),
             "mail_neirong" => stripslashes(htmlspecialchars_decode($mailNeirong)),
             "time_int"     => time(),
            ]
        );

        if ($rs) {
            $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "写公开信成功", "data" => $mailNeirong]);

        } else {
            $this->ajaxReturn(["zhuangtai" => 0, "tishi" => "写公开信失败"]);

        }
    }

    //获取单个用户的来往信件列表
    public function userMailList()
    {
        $userListKey = I("userListKey");
        $page = I("page");
        //获取所有信件内容
        $mailAll = Qita::getUserMailList($userListKey ,$page);


        if(intval($page)>$mailAll['tongji']['count_page']){
            $this->ajaxReturn(["zhuangtai" => 0, "tishi" => "没有了" , "data" => $mailAll]);
        }else{
            $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "用户来往邮件列表", "data" => $mailAll]);
        }

    }

    public function userMailList备用()
    {
        $userListKey = I("userListKey");

        //获取所有信件内容
        $mailAll = Qita::getUserMailList($userListKey);
        //dump($mailAll);
        $this->ajaxReturn(["zhuangtai" => 1,
                           "tishi"     => "获取用户来往信件列表成功",
                           "data"      => $mailAll
        ]);

    }

    //发送一封私人信件
    //1.得到userList 表里的key
    //2.那到key以后,将用户写的信和这个key一块写到数据库
    public function addMailOne()
    {
        //先判断这个session时间是否存在
        if(session("addMailOneTime")){

            //如果时间已经存在,则查看系统现在的时间和session时间相差是否超过3小时

        }

        //设置一个session时间
        session("addMailOneTime",time());

//        $userID = I('userID'); //发信人id
        $userID = session("userID"); //发信人id
        $toUserID = I('toUserID');//获取浏览器传过来的收信人id

        //获取公开信id,如果用户从公开信页面写的私人信件,就在数据库里增加这个id,以确定用户首次写信时从什么地方
        $openMailID = Verif::canshu(I('openMailID'), 'openMailID', false, 'int');

        $mailNeirong = Verif::canshu(I('mailNeirong'), 'mailNeirong', true,'mailNeirong'); //邮件内容




        //检测是否给自己写信 .如果发信人id 和收信人id一样的话,返回错误
        Verif::ziji($userID, $toUserID);

        //获取key .这个要放在最后执行
        $mailKey = Qita::getUserListKey($userID, $toUserID);

        if($openMailID){
            //检测用户是否已经给这封公开信写过信
            $openMailjiance=Db::dbfindOne('UserMailList',['faxin_user_id'=>$userID , 'open_mail_id'=>$openMailID]);
            if($openMailjiance){
                exit(json_encode(['zhuangtai' => 0, 'tishi' => '你已经给这封公开信回过信了']));

            }
        }


        //将信件写入数据库
        Db::dbSave("UserMailList", [
            'mail_key'        => $mailKey,
            'faxin_user_id'   => $userID,
            'shouxin_user_id' => $toUserID,
            'mail_neirong'    => stripslashes(htmlspecialchars_decode($mailNeirong)),
            'open_mail_id'    => $openMailID,
            'faxin_time_int'  => time(),
        ]);

        //增加 user_list 的new_mail_count数
        Count::newMailCountAdd(['user_id'=>$toUserID , 'mail_key'=>$mailKey]);

        $this->ajaxReturn(['zhuangtai' => 1, 'tishi' => '发信成功']);

    }

    //获取用户的未读邮件, 查找user_mail_list表里收件人id是当前登陆用户,并且 shouxin_time_int 为空的数据
    public function userMailWeidu()
    {

//        $userID = Verif::canshu(I('userID'), 'userID', true, 'int');
        $userID = session("userID");

        //方法1 ,只获未读邮件
        $mailList = Db::dbSelect("UserMailList", ['shouxin_user_id' => $userID, 'shouxin_time_int' => 0]);
        $count = count($mailList); //获取未读邮件数量

        //获取发信人的用户信息
        foreach ($mailList as $key => $val) {
            //获取发信人信息 ,把用户资料加到新的字段里
            $mailList[$key]['faxin_user'] = Qita::findUser($mailList[$key]['faxin_user_id']);
            $mailList[$key]['mail_neirong'] = strip_tags($mailList[$key]['mail_neirong']);;
            //dump($mailAll);
        }

        $this->ajaxReturn(['zhuangtai' => 1, 'tishi' => '获取用户未读邮件成功', 'data' => [
            'count' => $count, 'mailList' => $mailList]]);
    }


    //获取一封私人邮件,如果 shouxin_user_id =0的话,就更新 收信时间
    public function getuserMailOne()
    {
        $mailID = Verif::canshu(I('mailID'), 'mailID', true, 'int');
//        $userID = Verif::canshu(I('userID'), 'userID', true, 'int');
        $userID = session("userID");


        //获取一封私人邮件,
        $UserMailList = M('UserMailList');

        //如果发信人id 或者 收信人id 都是当前登陆用户,用户才能查看这封邮件
        $where['faxin_user_id'] = $userID;
        $where['shouxin_user_id'] = $userID;
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $map['id'] = $mailID;
        $mail = $UserMailList->where($map)->find();
        //dump($mail);
        if ($mail) {
            if ($mail['shouxin_time_int'] == 0) {

                //更新收信时间
                $UserMailList->save(['id' => $mailID, 'shouxin_time_int' => time()]);


                //减少 user_list 的new_mail_count数
                Count::newMailCountJian(['user_id'=>$mail['shouxin_user_id'] , 'mail_key'=>$mail['mail_key']]);
            }

            //获取发信人信息 ,把用户资料加到新的字段里
            $mail['faxin_user'] = Qita::findUser($mail['faxin_user_id']);

            //更新数组里的收信时间,
            $mail['shouxin_time_int']=time();


            $this->ajaxReturn(['zhuangtai' => 1, 'tishi' => '获取一封私人邮件成功', 'data' => $mail]);
        } else {
            $this->ajaxReturn(['zhuangtai' => 0, 'tishi' => '什么都没有']);
        }


    }


    //获取当前用户的好友列表
    public function friendList()
    {

        $list = Qita::getFriendList(session("userID"));
        $this->ajaxReturn(['zhuangtai' => 1, 'tishi' => '获取好友列表成功', 'data' => $list]);
    }


    //获取登陆用户信息
    public function getUserData(){
        $user=Qita::findUser(session('userID'));
        $this->ajaxReturn(['zhuangtai' => 1, 'tishi' => '获取用户信息成功', 'data' => $user]);
    }

    //登陆用户访问首页
    public function userIndex()
    {
        $userData = Qita::findUser(I("userID"));
        $this->ajaxReturn(["zhuangtai" => 1, "tishi" => "用户资料", "data" => $userData]);

    }


    //获取用户分组
    public function getUserGrop(){
        Quanxian::init(I('toUserID'));
    }
}