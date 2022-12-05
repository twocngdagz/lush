<?php

namespace App\Services\RealWinSolution\Commands;

use App\Account;
use App\Services\RealWinSolution\Models\RealWinConnection;
use Illuminate\Console\Command;

class SyncRealWinConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-realwin-connection
                            {account-id : Account ID to relate properties to.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update realwin solution connection';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $account_id = $this->argument('account-id');
        $account = Account::find($account_id);
        if (!$account) {
            $this->output->error('No account found with ID ' . $account_id);
            return false;
        }

        $url = $this->ask('What is the RealWin Solution URL?');
        $player = $this->ask('What is the Test Player ID to use when testing?', 1234);

        $account->realWinConnectorSettings()->create([
            'url' => $url,
            'test_player' => $player
        ]);

        $this->info('Successfully added the connection');
    }
}