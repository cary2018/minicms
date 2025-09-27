<?php
/**
 *
 * php think socket start -d   守护进程启动
 */
declare (strict_types = 1);

namespace app\command;


use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;
use PHPSocketIO\SocketIO;

class Socket extends Command
{
    // 全局数组保存uid在线数据
    private $userInfo = [];
    private $clientTotal = [];
    protected function configure()
    {
        // 指令配置
        $this->setName('socket')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
            ->addOption('host', 'H', Option::VALUE_OPTIONAL, 'the host of workerman server.', null)
            ->addOption('port', 'p', Option::VALUE_OPTIONAL, 'the port of workerman server.', null)
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the workerman server in daemon mode.')
            ->setDescription('Workerman Server for ThinkPHP');
    }

    protected function execute(Input $input, Output $output)
    {
        $action = $input->getArgument('action');

        if (DIRECTORY_SEPARATOR !== '\\') {
            if (!in_array($action, ['start', 'stop', 'reload', 'restart', 'status', 'connections'])) {
                $output->writeln("<error>Invalid argument action:{$action}, Expected start|stop|restart|reload|status|connections .</error>");
                return false;
            }

            global $argv;
            array_shift($argv);
            array_shift($argv);
            array_unshift($argv, 'think', $action);
        } elseif ('start' != $action) {
            $output->writeln("<error>Not Support action:{$action} on Windows.</error>");
            return false;
        }

        if ('start' == $action) {
            $output->writeln('Starting Workerman server...');
        }

        // 开启守护进程模式
        if ($this->input->hasOption('daemon')) {
            Worker::$daemonize = true;
        }

        // 传入ssl选项，包含证书的路径
        $context = array(
            'ssl' => array(
                'local_cert'  => root_path().'public'.'/ssl/socketSsl.pem',
                'local_pk'    => root_path().'public'.'/ssl/socketSsl.key',
                'verify_peer' => false,
            )
        );

        // 创建socket.io服务端，监听3120端口
        //$io = new SocketIO(3120,$context);
        $io = new SocketIO(Cfg('port'));
        // 当有客户端连接时打印一行文字
        $io->on('connection', function($socket)use($io){
            $socket->addedUser = false;
            // 定义chat message事件回调函数
            $socket->on('onlineUser', function($msg)use($io){
                // 触发所有客户端定义的chat message from server事件
                /*$info = GetSe('admin');
                $uid = $msg['uid'];*/
                //$io->to($uid)->emit('serverMessage', $msg);
                //信息发送
                //$io->emit('serverMessage', $msg);
                //用户上线
                $io->emit('online', $this->userInfo);
            });
            //统计在线浏览用户数量
            $socket->on('totalOnlineUser', function($msg)use($io){
                //新用户上线时向客户端发送统计信息
                $io->emit('totalOnline', count($this->clientTotal));
            });

            // 定义 sendMessage 事件回调函数
            $socket->on('sendMessage', function($msg)use($io){
                // 触发所有客户端定义的chat message from server事件
                //$info = GetSe('admin');
                $uid = $msg['uid'];
                $group = $msg['group'];
                $msg['time'] = date('Y-m-d H:i:s');
                if($msg['group']){
                    //echo '私聊';
                    $io->to($uid)->emit('serverMessage', $msg);
                    $io->to($group)->emit('serverMessage', $msg);
                }else{
                    //echo '公共频道';
                    //信息发送
                    $io->emit('serverMessage', $msg);
                    //$io->emit('serverMessage', count($this->clientTotal));
                }
            });

            // 当客户端发出“添加用户”时，它将侦听并执行
            $socket->on('addUser', function ($username) use($socket){
                // 将这个连接加入到uid分组，方便针对uid推送数据
                if($username['uid']){
                    if(isset($socket->uid)){
                        return;
                    }
                    $socket->join($username['uid']);
                    $this->userInfo[$username['uid']] = $username;
                    $socket->uid = $username['uid'];
                    //向客户端推送用户上线信息
                    $socket->emit('online', $this->userInfo);
                }
                if ($socket->addedUser)
                    return;
                global $usernames, $numUsers;
                // 我们将用户名存储在该客户端的套接字会话中
                $socket->username = $username;
                ++$numUsers;
                $socket->addedUser = true;
                $socket->emit('login', array(
                    'numUsers' => $numUsers
                ));
                // 在全局范围内（所有客户端）回显一个人已连接的内容
                $socket->broadcast->emit('user joined', $this->userInfo);
            });
            //接收客户端信息---统计在线浏览用户数量
            $socket->on('totalUser', function ($username) use($socket){
                // 将这个连接加入到key分组，方便针对key推送数据
                if($username['key']){
                    $this->clientTotal[$username['key']] = $username;
                    $socket->key = $username['key'];
                    //用户上线并向客户端发送信息
                    $socket->emit('totalOnlineUser', count($this->clientTotal));
                }
            });
            // 当客户端发出“打字”时，我们将其广播给其他人
            $socket->on('typing', function () use($socket) {
                $socket->broadcast->emit('typing', array(
                    'username' => $socket->username
                ));
            });
            // 当客户端发出“停止键入”时，我们会将其广播给其他人
            $socket->on('stop typing', function () use($socket) {
                $socket->broadcast->emit('stop typing', array(
                    'username' => $socket->username
                ));
            });

            // 当用户断开连接时。。执行此操作（一般是关闭网页或者跳转刷新导致）
            $socket->on('disconnect', function () use($socket) {
                //$socket->emit('outline', $socket->uid);
                // 全局响应此客户端已离开
                //$socket->broadcast->emit('outline', $socket->uid);
                if (!isset($socket->uid)) {
                    //return;
                }
                // 全局响应此客户端已离开
                $socket->broadcast->emit('outline', $socket->uid);

                //离开分组
                //$socket->leave($socket->uid);
                //删除离线用户
                unset($this->userInfo[$socket->uid]);
                //清除离开浏览用户
                unset($this->clientTotal[$socket->key]);
                //向客户端发送统计在线浏览用户
                $socket->broadcast->emit('totalOutline', count($this->clientTotal));
                //客户端ip
                //$clientIp = $socket->conn->remoteAddress;
                //echo '用户离开：'.$socket->key;
            });
            //客户端ip
            //$clientIp = $socket->conn->remoteAddress;
            //echo $socket->key."new connection coming--{$clientIp}\n";
        });
        // 监听一个http端口，通过http协议访问这个端口可以向所有客户端推送数据(url类似http://ip:9191?msg=xxxx)
        $io->on('workerStart', function()use($io) {
            $inner_http_worker = new Worker(request()->scheme().'://'.Cfg('host').':'.Cfg('httpPort'));
            $inner_http_worker->onMessage = function($http_connection, $data)use($io){
                if(!isset($_GET['msg'])) {
                    return $http_connection->send('fail, $_GET["msg"] not found');
                }
                $io->emit('chat message', $_GET['msg']);
                // 触发所有客户端定义的chat message from server事件
                $io->emit('serverMessage', $_GET['msg']);
                $http_connection->send('ok');
            };
            $inner_http_worker->listen();
        });

        Worker::runAll();
        // 指令输出
        //$output->writeln('socket');
    }
}
