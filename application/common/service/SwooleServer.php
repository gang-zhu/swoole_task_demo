<?php
/**
 * Created by PhpStorm.
 * User: gang.zhu
 * Date: 2019/9/6 0006
 * Time: 09:56
 */

namespace app\common\service;


use think\Exception;
use think\facade\Log;
use think\swoole\Server;

class SwooleServer extends Server
{
    protected $host = '0.0.0.0';
    protected $port = 9508;
    protected $serverType = 'websocket';
    protected $mode = SWOOLE_PROCESS;
    protected $socketType = SWOOLE_SOCK_TCP;
    protected $option = [
        'worker_num'=> 4,
        'backlog'	=> 128,
        'task_worker_num' => 4
    ];

    public function onReceive($serv, $fd, $from_id, $data)
    {
        $task_id = $serv->task($data);
        echo "开始投递异步任务 id=$task_id\n";
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        try {
            echo "接收异步任务[id=$task_id]".PHP_EOL;
            $db = new SwooleMysql();
            $data = $db->exec("select * from lp_activity where id = 1");
            echo "参数".json_encode($data).PHP_EOL;
        } catch(\Exception $e) {
            Log::debug('swoole测试');
        }
        $serv->finish("-> OK");
    }

    public function onFinish($serv, $task_id, $data)
    {
        echo "异步任务[id=$task_id]完成".PHP_EOL;
    }

    public function onMessage($serv, $frame)
    {
        echo "onMessage\n";
    }
    public function onStart($serv)
    {
        echo "start\n";
    }
    public function onClose($ser, $fd) {
        echo "client {$fd} closed\n";
    }
}