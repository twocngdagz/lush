@extends('layouts.app')
@section('secondary-nav')
    @include('settings.nav')
@endsection
@section('content')
    <header id="page-header">
        <h1>Add Rating</h1>
        <p>Lush Player Management rating settings</p>
    </header>

    <!-- TODO: Move this form into a separate editor page. -->
    {{ Form::open(['route' => ['settings.lush.ratings.store']]) }}
    @include('layouts.formErrors')
    <fieldset>
        <label class="required">Player Name:</label>
        <p></p>
        {{ Form::select('player_id',$players->pluck('name', 'id') ,null, ['placeholder' => 'Pick a player']) }}

    </fieldset>
    <fieldset>
        <label class="required">Play Type:</label>
        <p></p>
        {{ Form::select('play_type',['slot' => 'Slot', 'pit' => 'Pit'] ,null) }}
    </fieldset>

    <fieldset>
        <label class="required">Points Earned:</label>
        <p></p>
        {{ Form::text('points_earned', null) }}
    </fieldset>

    <fieldset>
        <label class="optional">Cash In $:</label>
        <p></p>
        {{ Form::text('cash_in', null) }}
    </fieldset>

    <fieldset>
        <label class="optional">Theo In $:</label>
        <p></p>
        {{ Form::text('theo_win', null) }}
    </fieldset>
    <fieldset>
        <label class="optional">Actual Win $:</label>
        <p></p>
        {{ Form::text('actual_win', null) }}
    </fieldset>
    <fieldset>
        <label class="optional">Comp Earned $</label>
        <p></p>
        {{ Form::text('comp_earned', null) }}
    </fieldset>
    <fieldset>
        <div class="col col-50">
            <label class="required">Play Start At Date</label>
            <date-picker name="play_start_at_date"
                         placeholder="Pick a date"
                         value=""/>
        </div>
        <div class="col col-50">
            <label class="required">Play Starts At Time</label>
            <input type="time"
                   name="play_start_at_time"
                   value=""
                   />
        </div>
    </fieldset>
    <fieldset>
        <div class="col col-50">
            <label class="required">Play Ends At Date</label>
            <date-picker name="play_end_at_date"
                         placeholder="Pick a date"
                         value=""/>
        </div>
        <div class="col col-50">
            <label class="required">Play Ends At Time</label>
            <input type="time"
                   name="play_end_at_time"
                   value=""
            />
        </div>
    </fieldset>

    <fieldset class="submit-group">
        {!! Form::submit('Add Rating') !!}
    </fieldset>
    {{ Form::close() }}
@endsection
