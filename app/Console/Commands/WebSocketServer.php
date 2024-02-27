<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\Chat;

class WebSocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the WebSocket server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $serve = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Chat()
                )
            ),
        8035);
        $serve->run();
//        return Command::SUCCESS;
    }
}
