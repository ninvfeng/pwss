# PHP WebSocket Server 
## 一个基于swoole的简单PHP websocket 服务
- 仅定义数据收发,不限格式,可用于在线聊天即时通知等
- 使用mongodb提供持久化存储
- 简洁高效,不到100行的核心代码,方便二次开发

## 依赖
- php7
- swoole 扩展
- mongodb php-mongodb扩展

## 安装
1. 拉取代码  ``` git clone https://github.com/ninvfeng/pwss.git ```
2. composer 安装mongodb依赖 ``` composer install ```
3. config.php 中配置mongodb数据库连接
4. 执行 ``` php server.php ``` 启动服务器

## 使用
### 可查看demo文件夹下的一个在线聊天的简单例子
1. 建立连接 ws = new WebSocket("ws://localhost:8012");
2. 初始化 成功返回token和已注册用户信息 ws.send('type=init&appid=应用标识&userid=当前用户ID&其他自定义参数') 如: ws.send('type=init&appid=webim&userid=1&username=ninvfeng')
3. 发送数据 ws.send('type=msg&appid=应用标识&token=带上初始化时返回的&to=目标用户ID&data=具体发送内容') 如: ws.send('type=msg&appid=webim&token=5a7d04909065a35679074913&to=2&data["content"]=helloworld'), 服务将把data原样发送到目标用户,目标用户收到后即可对信息进行处理
