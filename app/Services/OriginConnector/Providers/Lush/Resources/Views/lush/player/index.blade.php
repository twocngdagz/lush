@extends('layouts.app')

@section('content')

    <div class="row">
        <header id="page-header">
            <div class="row">
                <div class="col col-65">
                    <h1>Lush Players</h1>
                    <p>Manage the players.</p>
                </div>
            </div>
        </header>
        <br/>
        <div class="row">
            @can('create-offers')
                <a href="{{ route('settings.lush.players.create') }}" class="btn floatleft"><i class="fa fa-plus"></i> New Player</a>
            @endcan
        </div>
        @if($players->isEmpty())
            <div class="app-notification inline app-light">
                <p>No Players.</p>
            </div>
        @else
            <table>
                <thead>
                <th>ID</th>
                <th>Name</th>
                <th>Rank</th>
                <th class="center">Is Excluded</th>
                <th class="center">Points</th>
                <th class="center">Comp</th>
                <th class="center">Promo</th>
                <th class="center">Action</th>
                </thead>
                <tbody>
                @foreach($players as $player)
                    <tr>
                        <td>{{$player->id}}</td>
                        <td><strong>{{ $player->name }}</strong></td>
                        <td>{{ $player->lushrank->name }}</td>
                        <td class="center">{{ $player->is_excluded ? 'Yes' : 'No' }}</td>
                        <td class="center">{{ $player->lushaccounts->firstWhere('type', 'points')->balance ?? 0 }}</td>
                        <td class="center">{{ '$' . number_format($player->lushaccounts->firstWhere('type', 'comps')->balance ?? 0, 2) }}</td>
                        <td class="center">{{ '$' . number_format($player->lushaccounts->firstWhere('type', 'promo')->balance ?? 0, 2) }}</td>
                        <td class="center">
                                <a href="{{ route('settings.lush.players.edit', [$player]) }}">Edit</a> |
                                <a href="{{ route('settings.lush.players.delete', [$player]) }}" class="txt-attn confirm" data-confirm-message="Are you sure you want to delete this player?"> Delete</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $players->links() }}
        @endif
    </div>
@endsection
