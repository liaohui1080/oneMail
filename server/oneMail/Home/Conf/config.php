<?php
$serverType=php_uname('s'); //获取服务器类型
if($serverType=='Darwin'){
    $hosts='127.0.0.1';
}else{
    $hosts='localhost';
}

return array(
    /* 数据库设置 */
    'DB_TYPE'                => 'mysql', // 数据库类型
    'DB_HOST'                => $hosts, // 服务器地址
//    'DB_HOST'                => '127.0.0.1', // 服务器地址
    'DB_NAME'                => 'one_mail', // 数据库名
    'DB_USER'                => 'root', // 用户名
    'DB_PWD'                 => '13525232487', // 密码
    'DB_PORT'                => '3306', // 端口
    'DB_PREFIX'              => 'mail_', // 数据库表前缀
    'DB_DEBUG'               => false, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'        => true, // 启用字段缓存
    'DB_CHARSET'             => 'utf8', // 数据库编码默认采用utf8

    /* URL设置 */
    'URL_CASE_INSENSITIVE'   => false, // 默true 表示URL不区分大小写 false则表示区分大小写
    'URL_MODEL'              => 2, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式

    'MULTI_MODULE'           => false, // 是否允许多模块 如果为false 则必须设置 DEFAULT_MODULE

    // 加载扩展配置文件
    'LOAD_EXT_CONFIG' => '',

//    'DEFAULT_MODULE'         => 'cgi_home', // 默认模块


    'LOG_RECORD'            =>  true,   // 默认不记录日志
    'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_EXCEPTION_RECORD'  =>  false,    // 是否记录异常信息日志
);