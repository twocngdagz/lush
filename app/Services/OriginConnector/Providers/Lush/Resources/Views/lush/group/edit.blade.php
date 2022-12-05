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
    {{ Form::open(['route' => ['settings.lush.groups.update', $group->id]]) }}
    @include('layouts.formErrors')
    <fieldset>
        <label class="required">Group Name:</label>
        <p></p>
        {{ Form::text('name', $group->name) }}

    </fieldset>
    <fieldset>
        <div class="col col-50">
            <label class="required">Starts At Date</label>
            {!! Form::text('starts_at_date' ,$group->starts_at_date, ['data-toggle' => 'datepicker', 'placeholder' => 'Select a start date']) !!}
        </div>
        <div class="col col-50">
            <label class="required">Start At Time</label>
            <input type="time" name="starts_at_time"  step="60" value="{{$group->starts_at_time}}">
        </div>
    </fieldset>
    <fieldset>
        <div class="col col-50">
            <label class="required">Ends At Date</label>
            {!! Form::text('ends_at_date' ,$group->ends_at_date, ['data-toggle' => 'datepicker', 'placeholder' => 'Select a start date']) !!}
        </div>
        <div class="col col-50">
            <label class="required">End At Time</label>
            <input type="time" name="ends_at_time"  step="60" value="{{$group->ends_at_time}}">
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
    <fieldset>
        <label class="optional"><i class="fa fa-group pad-right"></i> Players </label>
        <table>
            <thead>
            <th>ID</th>
            <th>NAME</th>
            <th> ACTIONS </th>
            </thead>
            <tbody>
            @foreach($groupPlayers as $player)
                <tr>
                    <td>{{ $player->id }}</td>
                    <td>{{ $player->name }}</td>
                    <td>
                        <a href="{{ route('settings.lush.groups.player.remove', [$group, $player]) }}" class="txt-attn confirm" data-confirm-message="Are you sure you want to remove this player from the group?"> Remove</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </fieldset>

    <fieldset class="submit-group">
        {!! Form::submit('Update Group') !!}
    </fieldset>
    {{ Form::close() }}
@endsection
