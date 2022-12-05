@extends('layouts.app')
@section('breadcrumb')
    <ul class="breadcrumb">
        <li><a href="{{ route('settings.lush.players.index') }}">Players</a></li>
        <li><span>Create Player</span></li>
    </ul>
@endsection
@section('content')

    <header id="page-header">
        <nav class="tertiary-nav">
            <a href="{{ route('settings.lush.players.index') }}" class="btn btn-cancel"><i class="fa fa-ban"></i>Cancel</a>
        </nav>
        <h1><i class="fa fa-user pad-right"></i> Create Player</h1>
        <p>Enter the player info below to add the player </p>
    </header>
    {!! Form::open(['route' => ['settings.lush.players.store']]) !!}
    @include('layouts.formErrors')
    <div class="">
        @include('origin::lush.partials.players-form', [
            'uses_criteria' => false,
            'can_add_multiple' => false,
            'single' => true
        ])
    </div>
    <fieldset class="submit-group">
        {!! Form::submit('Save player') !!}
    </fieldset>
    {!! Form::close() !!}
@endsection
