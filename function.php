<?php
/*
* 系统函数
*/

//调试函数
function dump($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

//调试函数, 将数据记录到debug表
function debug($data){
    if(is_array($data)){
        $data=json_encode($data);
    }
    return db('debug')->insert(['data'=>$data,'created_at'=>date('Y-m-d H:i:s')]);
}

//读取配置文件
function config($key=''){
    static $config;
    if(!$config){
        $config=require 'config.php';
    }
    $key=explode('.',$key);
    $res=$config;
    foreach($key as $k => $v){
        $res=$res[$v];
    }
    return $res;
}

//快速实例化mongodb
function mongodb($table='test'){
    static $_mongodb=[];
    if(!empty($_mongodb[$table])){
        return $_mongodb[$table];
    }else{
        $_mongodb[$table]=new \ninvfeng\mongodb($table,config('mongodb'));
        return $_mongodb[$table];
    }
}
