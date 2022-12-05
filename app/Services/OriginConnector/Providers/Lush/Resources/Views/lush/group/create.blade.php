@extends('layouts.app')
@section('secondary-nav')
    @include('settings.nav')
@endsection
@section('content')
    <header id="page-header">
        <h1>Add Group</h1>
        <p>Lush Player Management group settings</p>
    </header>

    <!-- TODO: Move this form into a separate editor page. -->
    {{ Form::open(['route' => 'settings.lush.groups.store']) }}
    @include('layouts.formErrors')
    <fieldset>
        <label class="required">Group Name:</label>
        <p></p>
        {{ Form::text('name', null) }}

    </fieldset>
    <fieldset>
        <div class="col col-50">
            <label class="required">Starts At Date</label>
            {!! Form::text('starts_at_date' ,null, ['data-toggle' => 'datepicker', 'placeholder' => 'Select a start date']) !!}
        </div>
        <div class="col col-50">
            <label class="required">Start At Time</label>
            <input type="time" name="starts_at_time"  step="60">
        </div>
    </fieldset>
    <fieldset>
        <div class="col col-50">
            <label class="required">Ends At Date</label>
            {!! Form::text('ends_at_date' ,null, ['data-toggle' => 'datepicker', 'placeholder' => 'Select a start date']) !!}
        </div>
        <div class="col col-50">
            <label class="required">End At Time</label>
            <input type="time" name="ends_at_time"  step="60">
        </div>
    </fieldset>
    <fieldset>
        <label class="optional"><i class="fa fa-group pad-right"></i> Add Players to Group </label>
        <div class="select-group">
            <select class="multiple-select" multiple="multiple" name="players[]">
                @foreach($players->pluck('name', 'id')->toArray() as $id => $name)
                    <option value="{{$id}}"@if(old('group_id', isset($promotion) ? $promotion->restrictionGroups->pluck('group_id')->toArray() : null) && in_array($id, old('group_id', isset($promotion) ? $promotion->groups->pluck('group_id')->toArray() : null))) selected="selected"@endif>{{$name}}-{{$id}}</option>
                @endforeach
            </select>
        </div>
    </fieldset>

    <fieldset class="submit-group">
        {!! Form::submit('Add Group') !!}
    </fieldset>
    {{ Form::close() }}
@endsection
