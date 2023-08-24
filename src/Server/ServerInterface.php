<?php

declare(strict_types=1);

namespace Rebuild\Server;

use Swoole\Coroutine\Server as SwooleCoServer;
use Swoole\Server as SwooleServer;

interface ServerInterface
{
    public const SERVER_HTTP = 1;

    public const SERVER_WEBSOCKET = 2;

    public const SERVER_BASE = 3;

    public function init(array $config): ServerInterface;

    public function start();

    /**
     * @return SwooleCoServer|SwooleServer
     */
    public function getServer();
}
