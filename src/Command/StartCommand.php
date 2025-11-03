<?php

declare(strict_types=1);

namespace Rebuild\Command;

use Rebuild\Server\ServerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    /**
     * @var \Rebuild\Config\Config
     */
    protected $config;

    public function __construct(\Rebuild\Config\Config $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setName('start')->setDescription('启动服务');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $http = new \Swoole\Http\Server('0.0.0.0', 9501);

        $http->on('Request', function ($request, $response) {
            $response->header('Content-Type', 'text/html; charset=utf-8');
            $response->end('<h1>Hello Swoole. #' . random_int(1000, 9999) . '</h1>');
        });

        $http->start();

        //        $config = $this->config;
        //        $configs = $config->get('server');
        //        $serverFactory = new ServerFactory();
        //        $serverFactory->configure($configs);
        //        $serverFactory->getServer()->start();
        return 1;
    }
}
