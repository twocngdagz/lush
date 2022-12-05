<h3>Lush CMS Gateway Connection Details</h3>
<p>
    <label class="required">Connection URL:</label>
    <input type="text" name="lush_api_url" value="{{ old('lush_api_url', $account->connectorSettings->settings['lush_api_url']) }}">
</p>
<p>
    <label class="required">Api Key:</label>
    <input type="text" name="lush_api_key" value="{{ old('lush_api_key', $account->connectorSettings->settings['lush_api_key']) }}">
</p>
