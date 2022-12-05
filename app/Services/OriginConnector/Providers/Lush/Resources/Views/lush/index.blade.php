@extends('layouts.app')
@section('secondary-nav')
    @include('settings.nav')
@endsection
@section('content')
    <header id="page-header">
        <h1>Lush Management Settings</h1>

    </header>

    <div class="row">
        @include('layouts.formErrors')
        <phi-tabs>
            <phi-tab id="groups" icon="fa-tasks" name="Groups">
                <div>
                    <h3>Groups</h3>
                    <div class="clearfix">
                        <a href="{{ route('settings.lush.groups.create') }}" class="btn floatleft"><i class="fa fa-plus"></i> New Group.</a>
                        <div class="col col-35 right">
                            @include('partials.property-selector')
                        </div>
                    </div>
                </div>

                <hr/>
                <div>
                    @if($groups->isEmpty())
                        <div class="app-notification inline app-light">
                            <p>You do not have any groups.</p>
                        </div>
                    @else
                        <table>
                            <thead>
                            @if(showProperties())
                                <th>Property</th>
                            @endif
                            <th>ID</th>
                            <th>NAME</th>
                            <th>STARTS AT</th>
                            <th>ENDS AT</th>
                            <th> ACTIONS </th>
                            </thead>
                            <tbody>
                                @foreach($groups as $group)
                                    <tr>
                                        <td>{{ $group->id }}</td>
                                        <td>{{ $group->name }}</td>
                                        <td>{{ $group->starts_at->format('Y-m-d h:i:s A') }}</td>
                                        <td>{{ $group->ends_at->format('Y-m-d h:i:s A') }}</td>
                                        <td class="center">
                                            <a href="{{ route('settings.lush.groups.edit', [$group]) }}">Edit</a> |
                                            <a href="{{ route('settings.lush.groups.delete', [$group]) }}" class="txt-attn confirm" data-confirm-message="Are you sure you want to delete this player?"> Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $groups->links() }}
                    @endif
                </div>
            </phi-tab>
            <phi-tab id="ranks" icon="fa-bar-chart" name="Ranks">
                <div>
                    <h3>Ranks</h3>
                    <div class="clearfix">
                        <a href="{{ route('settings.lush.ranks.create') }}" class="btn floatleft"><i class="fa fa-plus"></i> New rank.</a>
                        <div class="col col-35 right">
                            @include('partials.property-selector')
                        </div>
                    </div>
                </div>

                <hr/>
                <div>
                    @if($ranks->isEmpty())
                        <div class="app-notification inline app-light">
                            <p>You do not have any ranks.</p>
                        </div>
                    @else
                        <table>
                            <thead>
                            @if(showProperties())
                                <th>Property</th>
                            @endif
                            <th>ID</th>
                            <th>NAME</th>
                            <th>THRESHOLD</th>
                            <th>PLAYERS COUNT</th>
                            <th> ACTIONS </th>
                            </thead>
                            <tbody>
                            @foreach($ranks as $rank)
                                <tr>
                                    <td>{{ $rank->id }}</td>
                                    <td>{{ $rank->name }}</td>
                                    <td>{{ $rank->threshold}}</td>
                                    <td>{{ $rank->lushplayers()->count()}}</td>
                                    <td>
                                        <a href="{{ route('settings.lush.ranks.edit', [$rank]) }}">Edit</a> |
                                        <a href="{{ route('settings.lush.ranks.delete', [$rank]) }}" class="txt-attn confirm" data-confirm-message="Are you sure you want to delete this rank?"> Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $ranks->links() }}
                    @endif
                </div>
            </phi-tab>
            @envIsNotProduction
            <phi-tab id="ratings" icon="fa-gear" name="Ratings">
                <div>
                    <h3>Ratings</h3>
                    <div class="clearfix">
                        <a href="{{ route('settings.lush.ratings.create') }}" class="btn floatleft"><i class="fa fa-plus"></i> New rating.</a>
                        <div class="col col-35 right">
                            @include('partials.property-selector')
                        </div>
                    </div>
                </div>

                <hr/>
                <div>
                    @if($ratings->isEmpty())
                        <div class="app-notification inline app-light">
                            <p>You do not have any ratings.</p>
                        </div>
                    @else
                        <table>
                            <thead>
                            @if(showProperties())
                                <th>Property</th>
                            @endif
                            <th>ID</th>
                            <th>Player</th>
                            <th>Play Type</th>
                            <th>Points Earned</th>
                            <th>Cash Win</th>
                            <th>Theo Win</th>
                            <th>Actual Win</th>
                            <th>Comp Earned</th>
                            <th>Gaming Date</th>
                            <th>Action</th>
                            </thead>
                            <tbody>
                            @foreach($ratings as $rating)
                                <tr>
                                    <td>{{ $rating->id }}</td>
                                    <td>{{ $rating->lushplayer->name }}</td>
                                    <td>{{ $rating->play_type }}</td>
                                    <td>{{ $rating->points_earned}}</td>
                                    <td>{{ '$'. number_format($rating->cash_in, 2)}}</td>
                                    <td>{{ '$'. number_format($rating->theo_win, 2)}}</td>
                                    <td>{{ '$'. number_format($rating->actual_win, 2)}}</td>
                                    <td>{{ '$'. number_format($rating->comp_earned, 2)}}</td>
                                    <td>{{ $rating->gaming_date}}</td>
                                    <td>
                                        <a href="{{ route('settings.lush.ratings.edit', [$rating]) }}">Edit</a> |
                                        <a href="{{ route('settings.lush.ratings.delete', [$rating]) }}" class="txt-attn confirm" data-confirm-message="Are you sure you want to delete this rating ?"> Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $ratings->links() }}
                    @endif
                </div>
            </phi-tab>
            @endenvIsNotProduction
        </phi-tabs>
    </div>
@endsection
@section('scripts')

@endsection
