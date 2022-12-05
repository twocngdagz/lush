<?php

namespace App\Services\RealWinSolution\Commands;

use App\Services\RealWinSolution\Contracts\WinInterface;
use Illuminate\Console\Command;

class WinConnection extends Command
{
    public $player;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:win';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Win Integration Connection';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->player = null;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(WinInterface $client)
    {
        $this->player = $client->validatePlayer();

        if ($this->player->id !== null) {
            return $this->info('Win connection established.');
        }

        return $this->error('Unable to established connection.');
    }
}
