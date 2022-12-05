@extends('layouts.app')
@section('secondary-nav')
    @include('settings.nav')
@endsection
@section('content')
    <header id="page-header">
        <h1>Edit Rank</h1>
        <p>Lush Player Management rank settings</p>
    </header>

    <!-- TODO: Move this form into a separate editor page. -->
    {{ Form::open(['route' => ['settings.lush.ranks.update', $rank->id]]) }}
    @include('layouts.formErrors')
    <fieldset>
        <label class="required">Rank Name:</label>
        <p></p>
        {{ Form::text('name', $rank->name) }}

    </fieldset>
    <fieldset>
        <label class="required">Threshold:</label>
        <p></p>
        {{ Form::text('threshold', $rank->threshold) }}
    </fieldset>

    <fieldset class="submit-group">
        {!! Form::submit('Update Rank') !!}
    </fieldset>
    {{ Form::close() }}
@endsection
