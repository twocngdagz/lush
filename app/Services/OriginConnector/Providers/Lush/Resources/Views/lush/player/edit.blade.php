@extends('layouts.app')

@section('breadcrumb')
    <ul class="breadcrumb">
        <li><a href="{{ route('settings.lush.players.index') }}">Players</a></li>
        <li><span>Edit Player</span></li>

    </ul>
@endsection

@section('content')

    <header id="page-header">
        <nav class="tertiary-nav">
            <a href="{{ route('settings.lush.players.index') }}" class="btn btn-cancel"><i class="fa fa-ban"></i>Cancel</a>
        </nav>
        <h1>Edit Player</h1>
        <p>Edit the player info below to update the player.</p>
    </header>

    {!! Form::open(['route' => ['settings.lush.players.update', $player], 'files' => true]) !!}
    @include('layouts.formErrors')

    <h3><i class="fa fa-trophy pad-right"></i>Edit Player</h3>
    <div class="rewards-list">
        @include('origin::lush.partials.players-form', [
            'uses_criteria' => false,
            'single' => true,
            'no_edit' => false,
            'players' => collect([$player]),
        ])
    </div>
    <fieldset class="submit-group">
        {!! Form::submit('Save player') !!}
    </fieldset>
    {!! Form::close() !!}
@endsection

