@extends('resources.views.layouts.app')
@section('secondary-nav')
    @include('resources.views.settings.nav')
@endsection
@section('content')
    <header id="page-header">
        <div class="row">
            <div class="col col-65">
                <h1>Property Settings</h1>
                <p>Details specific to your Origin Connector can be managed below.</p>
            </div>
            <div class="col col-35 right">
                @feature_available('global.multi-site')
                @if($authUser->isA('universal-marketing-director', 'super-admin') && $properties->count() > 1)
                    <form method="post" action="{{ route('settings.set-property') }}" class="filter-selector">
                        <label class="required">Selected Property</label>
                        <select name="property_id" class="select2" onchange="this.parentElement.submit();">
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}"
                                        @if(old('property_id', $selectedProperty->id) == $property->id) selected @endif>
                                    {{ $property->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
                @end_feature_available
            </div>
        </div>
    </header>
    @if(!$originProperty)
        @notify('error inline center', 'Cannot connect to the property. Please check the application server network configuration.')
    @endif
    <div class="data-ribbon">
        <div class="chunk">
            <h3>
                <i class="fa fa-{{ $originProperty ? 'check txt-success' : 'ban txt-attn' }}"></i>
            </h3>
            <p><strong>{{ $originProperty->name ?? 'Property connection' }}</strong> <br/>{{ $originProperty ? 'online' : 'offline' }}.</p>
        </div>
        <div class="chunk">
            @if($account->connectorSettings->settings['connection_test_player_id'])
                <h3>
                    <i class="fa fa-{{ $canGetPlayer ? 'check txt-success' : 'ban txt-light' }}"></i>
                </h3>
                <p><strong>Player connections </strong> <br/>{{ $canGetPlayer ? 'online' : 'offline' }}.</p>
            @else
                <h3>
                    <i class="fa fa-ban txt-light"></i>
                </h3>
                <p><strong>Enter a valid player id</strong>.</p>
            @endif
        </div>
        <div class="chunk">
            @if($account->connectorSettings->settings['connection_test_player_id'])
                <h3>
                    <i class="fa fa-{{ $canGetPlayerAccounts ? 'check txt-success' : 'ban txt-light' }}"></i>
                </h3>
                <p><strong>Player accounts </strong> <br/>{{ $canGetPlayerAccounts ? 'online' : 'offline' }}.</p>
            @else
                <h3>
                    <i class="fa fa-ban txt-light"></i>
                </h3>
                <p><strong>Enter a valid player id</strong>.</p>
            @endif
        </div>
    </div>
    <hr>
    <div class="row">
        @can('edit-settings')
            {!! Form::open(['route' => 'settings.connection.update']) !!}
            @include('resources.views.layouts.formErrors')
            <input type="hidden" name="property_id" value="{{ $selectedProperty->id }}"/>

            <div class="col col-50 offset-5">
                <h3>Connection Information</h3>

                <hr>
                <p><strong>Origin Connector</strong> : {{ Origin::version() }}</p>
                <p><strong>CMS API Version</strong> : {{ Origin::apiVersion() ?? '(Unavailable)' }}</p>

                @can('edit-settings')
                    <hr>
                    @include('origin::resources.views.settings.connection.index')

                    {!! Form::submit('Update Connection Information') !!}
                @endcan
            </div>

            <div class="col col col-40 offset-5 push-5">
                <h3>Test Connection Player Details</h3>
                <hr>
                <p>This is the ID we use for test connections to the Loyalty Rewards service.</p>
                {!! Form::text('connection_test_player_id', $account->connectorSettings->settings['connection_test_player_id'], ['placeholder' => 'Player ID']) !!}
                {!! Form::submit('Update Test Player ID', ['name' => 'update_player']) !!}
            </div>
        @endcan

        @can('edit-settings')
            {!! Form::close() !!}
        @endcan
    </div>
@endsection
