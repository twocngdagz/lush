<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Traits;

use Origin;
use App\Property;
use Illuminate\Http\Request;
use App\Models\Account\AccountConnectorSettings;

trait ConnectionSettingsTrait
{
    public function connectionSettingsIndex()
    {
        $account = auth()->user()->account;

        $properties = collect([]);
        $selectedProperty = Property::first();
        $canGetPlayer = parent::canGetPlayer($account->connectorSettings->settings['connection_test_player_id']);
        $canGetPlayerAccounts = parent::canGetPlayerAccounts($account->connectorSettings->settings['connection_test_player_id']);
        $originProperty = parent::canGetPropertyInfo();

        return view('settings.connection.index',
            compact('account', 'properties', 'canGetPlayer', 'canGetPlayerAccounts', 'originProperty', 'selectedProperty'));
    }

    public function connectionSettingsUpdate(Request $request)
    {
        $data = $request->validate([
            // Test player
            'connection_test_player_id' => 'nullable',

            // Phi Mock
            'mock_api_url' => 'required',
            'mock_api_key' => 'required',
        ]);

        // If update test player button pressed
        if ($request->update_player) {
            try {
                Origin::getPlayer($request->connection_test_player_id);
            } catch (\Exception $e) {
                return redirect()->route('settings.index')->with('app-error',
                    'We could not establish a connection with this player ID. It could be incorrect or the property may not be configured.');
            }
        }

        $account = auth()->user()->account;

        // We create a new account_connector_settings row each time we update
        // rather than update the same row so that we have a audit trail
        // of what was updated and who did the updating.
        AccountConnectorSettings::create([
            'account_id' => $account->id,
            'user_id' => auth()->user()->id,
            'settings' => [
                'mock_api_url' => $data['mock_api_url'],
                'mock_api_key' => $data['mock_api_key'],
                'connection_test_player_id' => $data['connection_test_player_id'],
            ]
        ]);

        return redirect()->route('settings.index')
            ->with('app-success', 'Your connection settings have been updated.');
    }

}
