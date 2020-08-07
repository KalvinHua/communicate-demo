<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use GatewayClient\Gateway;
use App\Message;
use App\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        //设置GatewayWorker服务的Register服务ip和端口
        Gateway::$registerAddress = '127.0.0.1:1238';
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $roomId = $request->room_id ? $request->room_id : '1';
        session()->put('room_id' , $roomId);

        return view('home');
    }

    /**
     * 用户登陆后 接收并与用户id绑定websocket给的client_id
     * @author Kalvin
     * @date   2020-08-05
     *
     * @param  Request
     */
    public function init(Request $request){

        //绑定用户
        $this->bindUser($request);

        //在线用户
        $this->getOnlineUsers();

        //获取历史消息
        $this->getChatHistory();

        //提示进入聊天室
        $this->sendLoginTip();
    }

    /**
     * 群发消息
     * @author Kalvin
     * @date   2020-08-07
     *
     * @param  Request
     */
    public function sendMessage(Request $request){

        //私聊给的用户的Id
        $sendToUserId = $request->input('user_id');

        //发送的聊天消息内容
        $content = $request->input('content');

        $data = [
            'name' => Auth::user()->name,
            'avatar' => Auth::user()->avatar(),
            'content' => $content,
            'time' => date('Y-m-d H:i:s',time())
        ];

        //判断是否为空
        if(empty($sendToUserId)){
            //群发
            // Gateway::sendToAll(json_encode(['type' => 'sendmessage' , 'data' => $data]));
            Gateway::sendToGroup(session('room_id'),json_encode(['type' => 'sendmessage' , 'data' => $data]));

            //存入数据库，以后可用于用户查询聊天记录
            Message::create([
                'user_id' => Auth::id(),
                'room_id' => session('room_id'),
                'content' => $content,
            ]);
        }else{
            //私聊,简易实现例子,不做
            $data['name'] = $data['name'].'('.Auth::user()->name.' To '.User::find($sendToUserId)->name.')';
            Gateway::sendToUid(Auth::id(),json_encode(['type' => 'sendmessage' , 'data' => $data]));
            //给发送的用户发消息
            Gateway::sendToUid($sendToUserId,json_encode(['type' => 'sendmessage' , 'data' => $data]));
        }
        
    }



    /**
     * 4、mvc后端bind.php收到client_id后利用GatewayClient调用Gateway::bindUid($client_id, $uid)
     * 将client_id与当前uid(用户id或者客户端唯一标识)绑定。
     * 如果有群组、群发功能，也可以利用Gateway::joinGroup($client_id, $group_id)将client_id加入到对应分组
     * @author Kalvin
     * @date   2020-08-05
     *
     * @param  obejct request
     */
    private function bindUser($request){
        //用户ID
        $userId = Auth::id();
        
        $clientId = $request->client_id;

        //绑定
        Gateway::bindUid($clientId,$userId);
        Gateway::joinGroup($clientId,session('room_id'));

        Gateway::setSession($clientId,[
            'id' => $userId,
            'avatar' => Auth::user()->avatar(),
            'name' => Auth::user()->name,
        ]);
    }

    /**
     * 获取在线用户
     * @author Kalvin
     * @date   2020-08-05
     *
     */
    private function getOnlineUsers(){
        $data = [
            'type' => 'onlineusers',
            // 'data' => Gateway::getAllClientSessions(),
            'data' => Gateway::getClientSessionsByGroup(session('room_id')),
        ];

        // Gateway::sendToAll(json_encode($data));
        Gateway::sendToGroup(session('room_id'),json_encode($data));
    }

    /**
     * 用户进入房间后向所有房间内发送消息提示
     * @author Kalvin
     * @date   2020-08-05
     *
     */
    private function sendLoginTip(){
        $data = [
            'name' => Auth::user()->name,
            'avatar' => Auth::user()->avatar(),
            'content' => '进入了聊天室',
            'time' => date('Y-m-d H:i:s',time())
        ];

        //令type为‘sendmessage’时，为发送消息
        // Gateway::sendToAll(json_encode(['type' => 'sendmessage' , 'data' => $data]));
        Gateway::sendToGroup(session('room_id'),json_encode(['type' => 'sendmessage' , 'data' => $data]));
    }

    private function getChatHistory(){
        $data = ['type' => 'history'];

        //信息表连接用户表,根据房间id显示当前房间的历史消息
        $messages = Message::with('user')->where('room_id',session('room_id'))->orderBy('id','asc')->limit(5)->get();


        //laravel自带方法 重新遍历数据生成新的数组
        $data['data'] = $messages->map(function($item,$key){
            return [
                'name' => $item->user->name,
                'avatar' => $item->user->avatar(),
                'content' => $item->content,
                'time' => $item->created_at->format('Y-m-d H:i:s')
            ];
        });

        Gateway::sendToUid(Auth::id(),json_encode($data));
    }
}
