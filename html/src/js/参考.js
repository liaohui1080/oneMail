var oneMail = angular.module("oneMail", ['ui.router']);
oneMail.config(function ($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.when("", "/index");
    $stateProvider
        .state("index", {
            url: "/index",
            templateUrl: "index/index.html",
            controller: function ($scope, $stateParams) {
                console.log($stateParams.id);
                //$scope.id = $stateParams.id;

            }
        })
        .state("openMail", {
            url: "/openMail`/{page}",
            templateUrl: "index/openMailList.html"
        })
        .state("openMailEnd", {
            url: "/openMailEnd",
            templateUrl: "index/openMailEnd.html"
        })
        .state("login", {
            url: "/login",
            templateUrl: "login/login.html"
        })
        .state("addUser", {
            url: "/addUser",
            templateUrl: "login/addUser.html"
        })
        .state("out", {
            url: "/out",
            templateUrl: "login/out.html"
        })
        //显示user首页
        .state("user", {
            url: '/user',
            templateUrl: "user/userIndex.html"
        })
        //显示user首页
        //.state("user.index", {
        //    url        : "/index",
        //    templateUrl: "user/openMailList.html"
        //})
        //所有公开信
        .state("user.openMail", {
            url: "/openMail/{page}",
            templateUrl: "user/openMailList.html"
        })
        .state("user.openMailEnd", {
            url: "/openMailEnd",
            templateUrl: "user/openMailEnd.html"
        })
        //单个用户来往信件
        .state("user.userMial", {
            url: "/userMail",
            templateUrl: "user/userMailList.html"
        })
        //选择写信对象
        .state("user.addMail", {
            url: "/addMail",
            templateUrl: "user/addMail.html"
        })
        //给所有人写信
        .state("user.addMailAll", {
            url: "/addMailAll",
            templateUrl: "user/addMailAll.html"
        })
        //给一个人写信
        .state("user.addMailOne", {
            url: "/addMailOne",
            templateUrl: "user/addMailOne.html"
        })
        //写信完成页面
        .state("user.addMailEnd", {
            url: "/addMailEnd",
            templateUrl: "user/addMailEnd.html"
        })
});
//用来存储全局变量,
var $canshu = {
    //当前登陆用户的信息
    user: null,

    //收信人的用户信息
    toUser: null,

    //存储当前浏览的页面,
    openMailPage: 'user/openMail/0',

    //储存当前公开信页面获取的公开信内容
    openMailData: null,

};

//默认的url参数
var $urlServer = "/server/home";
var $url = {
    index: $urlServer, //网站首页
    sigIn: $urlServer + "/index/sigIn", //登陆
    sigUp: $urlServer + "/index/sigUp", //登陆
    sigOut: $urlServer + "/index/sigOut", //退出
    userIndex: $urlServer + "/index/userIndex", //登陆用户首页
    addMailAll: $urlServer + "/index/addMailAll", //增加一封公开信
    openMail: $urlServer + "/index/openMail", //增加一封公开信
    userMailList: $urlServer + "/index/userMailList", //获取单个用户的来往信件列表
    addMailOne: $urlServer + "/index/addMailOne" //发送一封私人信件
};


var setCanshu = function (key, val) {
    $canshu[key] = val;
};
var getCanshu = function (key) {
    return $canshu[key];
};


/*  重写的 http方法,增加用户操作权限的判断,post只有按下面写才能提交给php
 *  http:$http, 把anglar 的$http对象传进来
 *  method:"post", //提交数据的方式 post  get
 *  url:$url.addMailAll,  //url地址
 *  data:{"mailNeirong": $scope.mailNeirong},  //提交的数据,要 json格式
 *  success: 提交数据成功以后的 回调方法
 *  error: 提交数据失败以后的 回调方法
 * */
function httpFn(o) {

    if (o.method == 'post') {

        o.http({
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            method: o.method,
            url: o.url,
            params: o.params,
            data: jQuery.param(o.data) //把json数据 序列化
        }).success(function (data, status, headers, config) {
            //判断权限,如果quanxian=1 则无权访问
            if (data.quanxian) {
                lhAlert(".alert-danger", {
                    maxTop: 20,
                    miniTop: 10,
                    time: 3000,
                    val: data.tishi
                });
                return this;
            } else {
                o.success(data, status, headers, config);
            }


        }).error(o.error);

    } else if (o.method == 'get') {
        o.http({
            method: o.method,
            url: o.url,
            params: o.data
        }).success(function (data, status, headers, config) {
            //判断权限,如果quanxian=1 则无权访问
            if (data.quanxian) {
                lhAlert(".alert-danger", {
                    maxTop: 20,
                    miniTop: 10,
                    time: 3000,
                    val: data.tishi
                });
                return this;
            } else {
                o.success(data, status, headers, config);
            }


        }).error(o.error);
    }
}


var index = function ($scope, $location, $http, $stateParams) {
    $http.get($url.index).success(function (data, status, headers, config) {
        console.log(data);
        //判断权限,如果quanxian=1 则无权访问
        if (data.quanxian) {
            $location.path('user/openMail/');
            return true;
        }


    });
    console.log("首页");
};
var userIndex = function ($scope, $stateParams, $location, $http) {
    //设置页面全屏
    $(".user-index-zuo-neirong , .user-index-you-neirong").height($(window).height());

    $("#header-ding")
        .css({
            left: ($(window).width() - $("#header-ding").width()) / 2
        });
    $http.get($url.userIndex).success(function (data, status, headers, config) {
        console.log(data);

        //判断权限,如果quanxian=1 则无权访问
        if (data.quanxian) {
            $location.path('/index');
            return true;
        }


        //给全局变量赋值当前登陆用户的信息
        $canshu.user = data.data;
        console.log($canshu.user)
        $scope.userName=getCanshu('user').name+getCanshu('user').id;
        $scope.openMail = function () {
            $location.path($canshu.openMailPage);
        }

    });
    console.log("user首页");
};

//只有用户能看到的邮件信息
var userMail = function ($scope, $stateParams, $location, $http) {
    console.log("userMail");
    $(".mail-neirong")
        .on("mouseenter", function () {
            $(this).find(".mail-image-1").stop(true).slideUp(300);
            $(this).find(".mail-image-2").stop(true).slideDown(300);
        })
        .on("mouseleave", function () {
            $(this).find(".mail-image-1").stop(true).slideDown(300);
            $(this).find(".mail-image-2").stop(true).slideUp(300);
        })
    $scope.openMail = function (id) {
        $(".mail-neirong").click(function () {
            $(this).find(".mail-image").hide();
            $(this).find(".mail-text").show();
            console.log("打开邮件" + id);
        });
    }


};
//user页面显示所有公开信件
var userOpenMail = function ($scope, $http, $stateParams, $location) {

    //如果url路径里的 page参数不存在,则跳转到首页
    if (!$stateParams.page) {
        $location.path('user/openMail/0');
        console.log("不存在");
    } else {
        $scope.page = {page: parseInt($stateParams.page) + 1};
        console.log("存在");
    }

    //存储当前浏览的页面
    $canshu.openMailPage = 'user/openMail/' + $stateParams.page;

    //获取一封公开信
    httpFn({
        http: $http,
        method: "get",
        url: $url.openMail,
        data: {"page": $stateParams.page},
        success: function (msg) {
            if (msg.zhuangtai) {
                console.log(msg);
                $scope.mailNeirong = msg.data.mail_neirong;
                $scope.userName = msg.data.user_id.name;
                $scope.date = msg.data.time_int;
                $scope.userID = msg.data.user_id.id;

                //给公开信的全局变量赋值,把整风公开信都存到变量里面
                setCanshu("openMailData", msg.data);

                //获取写这封公开信的人的信息, 并把他赋值给,收信人全局参数
                setCanshu("toUser", msg.data.user_id);


                //前往私人写信页面
                $scope.addMailOne = function () {
                    console.log("写信");

                    //判断是否给自己写信
                    if ($canshu.user.id == getCanshu('toUser').id) {
                        lhAlert(".alert-danger", {
                            maxTop: 60,
                            miniTop: 40,
                            time: 3000,
                            val: "你不能给自己写信"
                        });
                    } else {
                        //转到写信页面
                        $location.path('user/addMailOne');
                    }
                }


            } else { //如果用户浏览完所有公开信,则跳转到 没有页面
                $scope.mailNeirong = msg.tishi;
                $scope.page = {page: 0};
                $location.path('user/openMailEnd');
                console.log(msg.tishi);
            }
        }
    });

};

//index页面显示所有公开信件
var indexOpenMail = function ($scope, $http, $stateParams, $location) {
    //如果当前分页不存在则跳转到第一页,如果存在则给 下一页 按钮提前加1 以便于能获取下一页的数据
    if (!$stateParams.page) {
        $location.path('/openMail/0');
        console.log("不存在");
    } else {
        $scope.page = {page: parseInt($stateParams.page) + 1};
        console.log("存在");
    }

    //存储当前选中的页面,以便于登陆以后直接跳转到这里
    $canshu.openMailPage = 'user/openMail/' + $stateParams.page;

    //获取一封公开信
    httpFn({
        http: $http,
        method: "get",
        url: $url.openMail,
        data: {"page": $stateParams.page},
        success: function (msg) {
            if (msg.zhuangtai) {
                console.log(msg);
                $scope.mailNeirong = msg.data.mail_neirong;
                $scope.userName = msg.data.user_id.name;
                $scope.date = msg.data.time_int;
                $scope.userID = msg.data.user_id.id;
                $scope.addMailOne = function () {
                    $location.path('/login');
                    console.log("写信");
                    console.log($canshu);
                }
            } else {
                $scope.mailNeirong = msg.tishi;
                $scope.page = {page: 0};
                $location.path('/openMailEnd');
                console.log(msg.tishi);
            }
        }
    });


};
var addMailAll = function ($scope, $http, $location) {
    $scope.mailNeirong = '';
    console.log("公开信");

    //返回不写了
    $scope.backOpenMail = function () {
        $location.path($canshu.openMailPage);
    };

    //公开信发送按钮
    $scope.faMail = function () {
        console.log("发信")
        httpFn({
            http: $http,
            method: "post",
            url: $url.addMailAll,
            data: {"mailNeirong": $scope.mailNeirong},
            success: function (data) {
                console.log(data);
                $location.path('/user/addMailEnd');
            }
        });


    }
};
var addMailOne = function ($scope, $http, $location, $stateParams) {
        $scope.mailNeirong = '';
        $scope.userName = getCanshu('toUser').name;

        var toUserID = getCanshu('toUser').id;
        var openMailID = getCanshu('openMailData').id;

        //返回公开信页面
        $scope.backOpenMail = function () {
            $location.path($canshu.openMailPage);
        };

        //获取用户和这封公开信的人的来往邮件列表
        httpFn({
            http: $http,
            method: "get",
            url: $url.userMailList,
            data: {"toUserID": toUserID, "openMailID": openMailID},
            success: function (msg) {
                console.log(msg);
                //把用户邮件列表的key保存的全局变量里
                setCanshu("mailKey", msg.data.mailKey);

                //给模板赋值 信件列表
                $scope.mailList = msg.data.mailList;
            }
        });


        //发出信件方法
        $scope.faMail = function () {
            console.log("发信");
            console.log($canshu);
            var faData = {
                "toUserID": toUserID,
                "mailNeirong": $scope.mailNeirong,
                "mailKey": getCanshu('mailKey')
            };
            httpFn({
                http: $http,
                method: "post",
                url: $url.addMailOne,
                data: faData,
                success: function (data) {
                    console.log(data);
                    if (data.zhuangtai) {
                        $location.path('/user/addMailEnd');
                    } else {
                        lhAlert(".alert-danger", {
                            maxTop: 60,
                            miniTop: 40,
                            time: 3000,
                            val: data.tishi
                        });
                    }
                }
            });

        }

    }
    ;
function login($scope, $stateParams, $location, $http) {
    $scope.userMail = '';//为其指定一个初始值，这样在html里引用时才不会因为parent scope里没有找到`fen`变量而重新创建一个
    $scope.userPass = '';
    $scope.fnLogin = function () {


        $http.get($url.sigIn + '/userMail/' + $scope.userMail + '/userPass/' + $scope.userPass).success(function (data, status, headers, config) {
            console.log(data);


            if (data.zhuangtai) {
                //判断登陆以后要跳转的是那一页, 如果直接登陆,就跳转到user 首页, 如果带分页参数,就跳转到当前参数页
                if ($canshu.openMailPage != 'user/openMail/') {
                    $location.path($canshu.openMailPage);
                    console.log($canshu);
                } else {
                    $location.path('/user/openMail/');
                }
            } else {
                lhAlert(".alert-danger", {
                    maxTop: 60,
                    miniTop: 40,
                    time: 3000,
                    val: data.tishi
                });
            }
        });
    };
    console.log("登陆");
}
function out($scope, $stateParams, $location, $http) {
    console.log("退出");
    $http.get($url.sigOut).success(function (data, status, headers, config) {
        console.log(data);
        if (data.zhuangtai) {
            $scope.zhuangtai = data.tishi;
        }
    });
}
function addUser($scope, $stateParams, $location, $http) {
    $scope.userMail = '';//为其指定一个初始值，这样在html里引用时才不会因为parent scope里没有找到`fen`变量而重新创建一个
    $scope.userPass = '';
    $scope.sigUp = function () {
        httpFn({
            http: $http,
            method: "get",
            url: $url.sigUp,
            data: {"userMail": $scope.userMail, "userPass": $scope.userPass},
            success: function (data) {
                console.log(data);

                if (data.zhuangtai) {
                    //判断注册以后要跳转的是那一页, 如果直接注册,就跳转到user 首页, 如果带分页参数,就跳转到当前参数页
                    if ($canshu.openMailPage != 'user/openMail/') {
                        $location.path($canshu.openMailPage);
                        console.log($canshu);
                    } else {
                        $location.path('/user/openMail/');
                    }
                } else {
                    lhAlert(".alert-danger", {
                        maxTop: 30,
                        miniTop: 10,
                        time: 3000,
                        val: data.tishi
                    });
                }
            }
        });
    };
    console.log("增加用户");
}
//弹出提示
function lhAlert(div, obj) {
    var div = $(div);
    $(".alert").stop(true, true).hide(0);
    div.html(obj.val);
    div.css({
        top: 10,
        Zindex: 999,
        left: ($(window).width() - div.width()) / 2
    });
    div.stop(true, true).animate({
        top: obj.maxTop,
        opacity: 'show'
    }, 300);
    var st = setTimeout(function () {
        div.stop(true, true).animate({
            top: obj.miniTop,
            opacity: 'hide'
        }, 300);
    }, obj.time);


}

