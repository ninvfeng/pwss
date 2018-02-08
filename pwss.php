<?php

//基本设置
date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors','On');
define ("PATH", dirname(__FILE__));

//导入配置和数据库操作类
require PATH.'/vendor/autoload.php';
require PATH.'/function.php';

//创建websocket服务, 设置监听IP和端口
$ws = new swoole_websocket_server("0.0.0.0", 8012);

$ws->on('open', function ($ws, $request) {

});

$ws->on('message', function ($ws, $frame) {
    parse_str($frame->data,$data);

    //初始化,返回用户信息
    if($data['type']=='init'){
        mongodb('user')->where(['_id'=>$data['token']])->save(['sid'=>$frame->fd]);
        $from=mongodb('user')->where(['_id'=>$data['token']])->find();
        $msg['type']='init';
        $msg['data']=$from;
        $ws->push($frame->fd,http_build_query($msg));
    }

    //收发消息
    elseif($data['type']=='msg'){
        $from=mongodb('user')->where(['_id'=>$data['token']])->find();
        unset($from['_id']);
        $to=mongodb('user')->where(['module'=>$from['module'],'userid'=>$data['to']])->find();

        $msg['type']='msg';
        $msg['from']=$from;
        $msg['data']=$data['data'];
        $res=false;
        if($to['sid']){
            $res=$ws->push($to['sid'],http_build_query($msg));
        }else{
            $msg['type']='err';
            $msg['from']='system';
            $msg['data']='对方已离线';
            $ws->push($frame->fd,http_build_query($msg));
        }

        $log['from']=$from['id'];
        $log['to']=$to['id'];
        $log['data']=$data;
        $log['date']=date('Y-m-d H:i:s');
        $log['res']=$res;
        mongodb('msg')->add($log);
    }
});

$ws->on('close', function ($ws, $fd) {
    mongodb('user')->where(['sid'=>$fd])->save(['sid'=>0]);
});

$ws->start();
