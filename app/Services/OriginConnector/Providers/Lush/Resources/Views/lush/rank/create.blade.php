@extends('layouts.app')
@section('secondary-nav')
    @include('settings.nav')
@endsection
@section('content')
    <header id="page-header">
        <h1>Add Rank</h1>
        <p>Lush Player Management rank settings</p>
    </header>

    <!-- TODO: Move this form into a separate editor page. -->
    {{ Form::open(['route' => ['settings.lush.ranks.store']]) }}
    @include('layouts.formErrors')
    <fieldset>
        <label class="required">Rank Name:</label>
        <p></p>
        {{ Form::text('name', null) }}

    </fieldset>
    <fieldset>
        <label class="required">Threshold:</label>
        <p></p>
        {{ Form::text('threshold', null) }}
    </fieldset>

    <fieldset class="submit-group">
        {!! Form::submit('Add Rank') !!}
    </fieldset>
    {{ Form::close() }}
@endsection
