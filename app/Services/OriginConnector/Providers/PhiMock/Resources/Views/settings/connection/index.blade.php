<h3>Phi Mock CMS Gateway Connection Details</h3>
<p>
    <label class="required">Connection URL:</label>
    <input type="text" name="mock_api_url" value="{{ old('mock_api_url', $account->connectorSettings->settings['mock_api_url']) }}">
</p>
<p>
    <label class="required">Api Key:</label>
    <input type="text" name="mock_api_key" value="{{ old('mock_api_key', $account->connectorSettings->settings['mock_api_key']) }}">
</p>
