# communicate-demo

__build with laravel/workerman__

__use the ' php7.2 + laravel5.8 + mysql5.6 + websocket ' to build a demo which user can chat with orhers.
使用' php7.2 + laravel5.8 + mysql5.6 + websocket '实现的简单聊天室，支持用户房间聊天以及私聊等操作。__

__运行准备__

 * 安装组件
```
composer install
npm install
```

 * .env.example 改名使用命令 copy 修改为 .env
```
cp .env.example .env
```
 * 更改.env中的数据库配置为本地的配置

 * 迁移数据库
```
php artisan migrate
```

 * 启动GatewayWorker
```
cd socket/GatewayWorker
php start.php start
```

 * 项目目录启动
```
npm run watch-poll
```