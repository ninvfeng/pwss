# PHP WebSocket Server 
## 一个基于swoole的简单PHP websocket 服务
- 仅定义数据收发,不限格式,可用于在线聊天即时通知等
- 使用mongodb提供持久化存储
- 简洁高效,不到100行的核心代码,方便二次开发

## 安装
1. 拉取代码  ``` git clone https://github.com/ninvfeng/pwss.git ```
2. composer 安装mongodb依赖 ``` composer install ```
3. config.php 中配置mongodb数据库连接
4. 执行 ``` php server.php ```
