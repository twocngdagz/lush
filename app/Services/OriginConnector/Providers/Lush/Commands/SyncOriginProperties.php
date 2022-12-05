<?php

namespace App\Services\OriginConnector\Providers\Lush\Commands;

use App\Account;
use App\Kiosk;
use App\Models\Account\AccountConnectorSettings;
use App\Property;
use App\Services\OriginConnector\Facades\OriginFacade;
use App\User;
use Illuminate\Console\Command;

class SyncOriginProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-origin-properties
                            {account-id : Account ID to relate properties to.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync up local properties with all found from origin call.';

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
        $this->initializeConnectorSettings($account);
        $this->syncProperties($account);
        $this->assignDefaultPropertyToUsers();
        $this->assignDefaultPropertyToKiosks();
        $this->output->success('Process Complete');
    }

    protected function initializeConnectorSettings($account)
    {
        $connectorSettings = $account->connectorSettings;
        if (!$connectorSettings->settings || !isset($connectorSettings->settings['lush_api_url'])) {
            $this->output->newLine();
            $this->comment('Let\'s setup the Lush connector settings... To apply the default value (shown in brackets) hit enter.');
            $settings = [];
            $settings['lush_api_url'] = $this->ask('What is the Lush CMS API URL?', 'https://127.0.0.1');
            $settings['lush_api_key'] = $this->ask('What is the Lush CMS API key?', '12345');
            $settings['connection_test_player_id'] = $this->ask('What is the player ID for the player used to test the connection?');

            AccountConnectorSettings::create([
                'account_id' => $account->id,
                'settings' => $settings,
            ]);
        }
    }

    protected function syncProperties($account)
    {
        try {
            config(['services.connector.account_id' => $account->id]);
            config(['services.connector.property_id' => 1]);
            $property = OriginFacade::getPropertyInfo();
            if (is_null($property)) {
                $this->output->error('No properties found in origin call.');
            }

            $new_property = Property::firstOrCreate(['ext_property_id' => $property->id, 'account_id' => $account->id]);
            $new_property->account_id = $account->id;
            $new_property->name = $property->name;
            $new_property->timezone = (in_array($new_property->timezone, timezone_identifiers_list())) ? $new_property->timezone : $property->timezone;
            $new_property->save();

            $this->output->newLine();
            $this->info('Property successfully synced.');
        } catch (\Exception $e) {
            $this->output->error('Failed to retrieve property info. ' . $e->getMessage());
        }
    }

    protected function assignDefaultPropertyToUsers()
    {
        $this->output->newLine();
        $property = Property::first();
        User::all()->each(function (User $user) use ($property) {
            if ($user->properties()->count() == 0) {
                $user->properties()->attach($property->id);
                $user->save();
                $this->info($user->name . ' assigned to ' . $property->display_name . ' as default.');
            }
        });
    }

    protected function assignDefaultPropertyToKiosks()
    {
        $this->output->newLine();
        $property = Property::first();
        Kiosk::all()->each(function (Kiosk $kiosk) use ($property) {
            if (!$kiosk->property_id) {
                $kiosk->property_id = $property->id;
                $kiosk->save();
                $this->info($kiosk->name . ' assigned to ' . $property->display_name . ' as default.');
            }
        });
    }
}
