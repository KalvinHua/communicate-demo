<template>
    <div class="container">
        <a href="?room_id=1" class="btn btn-danger">吃货人生</a>
        <a href="?room_id=2" class="btn btn-primary">技术探讨</a>
        <hr class="divider">

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">聊天室</div>
                    <div class="card-body">
                        <div class="messages">
                            <div class="media" v-for="message in messages">
                                <div class="media-left">
                                    <a href="#">
                                        <img class="media-object img-circle" :src="message.avatar">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <p class="time">{{message.time}}</p>
                                    <h4 class="media-heading">{{message.name}}</h4>
                                    {{message.content}}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">聊天室在线用户</div>

                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item" v-for="user in users">
                                <img :src="user.avatar" class="img-circle">
                                {{user.name}}
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <form @submit.prevent="onSubmit">
            <div class="form-group">
                <label for="user_id">私聊</label>

                <select class="form-control" id="user_id" v-model="user_id">
                    <option value="">所有人</option>
                    <option :value="user.id" v-for="user in users">
                        {{user.name}}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">内容</label>
                <textarea class="form-control" rows="3" id="content" v-model="content"></textarea>
            </div>

            <button type="submit" class="btn btn-dark">提交</button>
        </form>
    </div>
</template>

<script>
    //1、网站页面建立与GatewayWorker的websocket连接
    let ws = new WebSocket("ws://127.0.0.1:7272");
    export default {
        data() {
            return {
                messages: [],
                content: '',
                users: [],
                user_id: '',
                //chat_users: [],
            }
        },
        created: function () {
            // 2、GatewayWorker发现有页面发起连接时，将对应连接的client_id发给网站页面
            // 服务端主动推送消息时会触发这里的onmessage
            ws.onmessage = (e) => {

                //JSON 字符串转换为对象
                let data = JSON.parse(e.data);
                
                //如果没有类型，就为空
                let type = data.type || '';

                switch (type) {
                    case 'ping':
                        ws.send('pong');
                        break;
                    //登陆
                    case 'init':
                        //3、网站页面收到client_id后触发一个ajax请求(假设是bind.php)将client_id发到mvc后端
                        axios.post('/init', {client_id: data.client_id})
                        break;
                    //发送消息
                    case 'sendmessage':
                        this.messages.push(data.data);
                        //自动滚动聊天
                        this.$nextTick(function () {
                            $('.card-body').animate({scrollTop: $('.messages').height()});
                        })
                        break;

                    case "login":
                        this.messages.push(data.data)
                        break;

                    case 'onlineusers':
                        this.users = data.data;
                        break;

                    case 'history':
                        this.messages = data.data;
                        break;

                    case 'logout':
                        //退出
                        this.$delete(this.users, data.client_id)
                        break;

                    default:
                        break;
                }
            }
        },
        methods: {
            onSubmit() {
                if (this.content == '') {
                    alert("请输入聊天内容")
                    return;
                }
                axios.post('/sendmessage', {content: this.content, user_id: this.user_id})
                this.content = ''
            }
        }
    }
</script>


<style scoped>
    .card-body {
        height: 480px;
        overflow: auto;
    }
    .media-object.img-circle {
        width: 64px;
        height: 64px;
    }
    .img-circle {
        width: 48px;
        height: 48px;
    }
    .time {
        float: right;
    }
    .media {
        margin-top: 24px;
    }
</style>