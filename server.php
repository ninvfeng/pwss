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

    //初始化用户 必须包含 appid&userid
    if($data['type']=='init'){
        unset($data['type']);
        if((!$data['appid'])||(!$data['userid'])){
            $ws->push($frame->fd,'初始化必须包含appid和userid');
        }else{
            if(mongodb('user')->where(['appid'=>$data['appid'],'userid'=>$data['userid']])->find()){
                $data['sid']=$frame->fd;
                mongodb('user')->where(['appid'=>$data['appid'],'userid'=>$data['userid']])->update($data);
            }else{
                $data['sid']=$frame->fd;
                mongodb('user')->insert($data);
            }
            $from=mongodb('user')->where(['appid'=>$data['appid'],'userid'=>$data['userid']])->find();
            $msg['type']='init';
            $msg['token']=$from['_id']['$oid'];
            unset($from['_id']);
            $msg['data']=$from;
            $ws->push($frame->fd,http_build_query($msg));
        }
    }

    //收发消息 必须包含 token和要发送的用户id
    elseif($data['type']=='msg'){
        $from=mongodb('user')->where(['_id'=>$data['token']])->find();
        unset($from['_id']);

        $msg['type']='msg';
        $msg['from']=$from;
        $msg['data']=$data['data'];$res=false;

        //群发
        if($data['to']=='all'){
            $online=mongodb('user')->where(['appid'=>$from['appid'],'sid'=>['$gt'=>0]])->select();
            foreach($online as $k => $v){
                if($v['sid'] != $frame->fd){
                    $res=$ws->push($v['sid'],http_build_query($msg));
                    if(!$res){
                        mongodb('user')->where(['sid'=>$v['sid']])->update(['sid'=>0]);
                    }
                }
            }
        }

        //单聊
        else{
            $to=mongodb('user')->where(['appid'=>$from['appid'],'userid'=>$data['to']])->find();

            if($to['sid']){
                $res=$ws->push($to['sid'],http_build_query($msg));
            }else{
                $msg['type']='err';
                $msg['from']='system';
                $msg['data']='对方已离线';
                $ws->push($frame->fd,http_build_query($msg));
            }            
        }

        //是否保存记录
        if($data['save']){
            $log['appid']=$from['appid'];
            $log['from']=$from['userid'];
            $log['to']=$to['userid'];
            $log['data']=$data;
            $log['date']=date('Y-m-d H:i:s');
            $log['res']=$res;
            mongodb('msg')->insert($log);
        }
    }
});

$ws->on('close', function ($ws, $fd) {
    mongodb('user')->where(['sid'=>$fd])->update(['sid'=>0]);
});

$ws->start();
