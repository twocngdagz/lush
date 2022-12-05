<?php

namespace App\Services\OriginConnector\Providers\Lush\Controllers;

use App\Country;
use App\Http\Controllers\Controller;
use App\Services\OriginConnector\Providers\Lush\DataTransferObjects\StoreLushPlayerDataTransferObject;
use App\Services\OriginConnector\Providers\Lush\Requests\StoreLushGroupRequest;
use App\Services\OriginConnector\Providers\Lush\Requests\StoreLushPlayerRequest;
use App\Services\OriginConnector\Providers\Lush\Requests\StoreLushRankRequest;
use App\Services\OriginConnector\Providers\Lush\Requests\StoreLushRatingRequest;
use App\Services\OriginConnector\Providers\Lush\Models\LushGroup;
use App\Services\OriginConnector\Providers\Lush\Models\LushPlayer;
use App\Services\OriginConnector\Providers\Lush\Models\LushRank;
use App\Services\OriginConnector\Providers\Lush\Models\LushRating;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index() {
        $lushGroups = LushGroup::paginate(15)->fragment('groups');
        $lushRanks = LushRank::paginate(15)->fragment('ranks');
        $lushRatings = LushRating::paginate(15)->fragment('ratings');
        return view('origin::lush.index')
            ->with('groups', $lushGroups)
            ->with('ranks', $lushRanks)
            ->with('ratings', $lushRatings);
    }

    public function createGroup()
    {
        $lushPlayers = LushPlayer::all();
        return view('origin::lush.group.create')->with('players', $lushPlayers);
    }

    public function storeGroup(StoreLushGroupRequest $request)
    {
        $startsAt = Carbon::createFromFormat('m/d/Y H:i', $request->get('starts_at_date') . ' ' . $request->get('starts_at_time'));
        $endsAt = Carbon::createFromFormat('m/d/Y H:i', $request->get('ends_at_date') . ' ' . $request->get('ends_at_time'));
        $lushGroup = new LushGroup();
        $lushGroup->name = $request->input('name');
        $lushGroup->starts_at = $startsAt;
        $lushGroup->ends_at = $endsAt;
        $lushGroup->save();
        $lushGroup->lushplayers()->attach($request->input('players'));
        return redirect()->route('settings.lush.index')->with('app-success', 'Your group has been added.');
    }

    public function editGroup(LushGroup $group)
    {
        $lushPlayers = LushPlayer::all();
        $groupPlayers = $group->lushplayers;
        return view('origin::lush.group.edit')
            ->with('group', $group)
            ->with('players', $lushPlayers)
            ->with('groupPlayers', $groupPlayers);
    }

    public function createRating()
    {
        $players = LushPlayer::all();
        return view('origin::lush.rating.create')->with('players', $players);
    }

    public function editRating(LushRating $rating)
    {
        $players = LushPlayer::all();
        return view('origin::lush.rating.edit')
            ->with('players', $players)
            ->with('rating', $rating);
    }

    public function storeRating(StoreLushRatingRequest $request)
    {
        $startAt  = Carbon::createFromFormat('m/d/Y H:i', $request->get('play_start_at_date') . ' ' . $request->get('play_start_at_time'));
        $endAt = Carbon::createFromFormat('m/d/Y H:i', $request->get('play_end_at_date') . ' ' . $request->get('play_end_at_time'));
        $rating = new LushRating();
        $rating->lush_player_id = $request->input('player_id');
        $rating->play_type = $request->input('play_type');
        $rating->points_earned = $request->input('points_earned');
        $rating->cash_in = $request->input('cash_in');
        $rating->theo_win = $request->input('theo_win');
        $rating->actual_win = $request->input('actual_win');
        $rating->comp_earned = $request->input('comp_earned');
        $rating->starts_at = $startAt;
        $rating->ends_at = $endAt;
        $rating->save();
        return redirect()->route('settings.lush.index.ratings')->with('app-success', 'Your rating has been added.');

    }

    public function updateRating(LushRating $rating, StoreLushRatingRequest $request)
    {
        $startAt  = Carbon::createFromFormat('m/d/Y H:i', $request->get('play_start_at_date') . ' ' . $request->get('play_start_at_time'));
        $endAt = Carbon::createFromFormat('m/d/Y H:i', $request->get('play_end_at_date') . ' ' . $request->get('play_end_at_time'));
        $rating->lush_player_id = $request->input('player_id');
        $rating->play_type = $request->input('play_type');
        $rating->points_earned = $request->input('points_earned');
        $rating->cash_in = $request->input('cash_in');
        $rating->theo_win = $request->input('theo_win');
        $rating->actual_win = $request->input('actual_win');
        $rating->comp_earned = $request->input('comp_earned');
        $rating->starts_at = $startAt;
        $rating->ends_at = $endAt;
        $rating->save();
        return redirect()->route('settings.lush.index.ratings')->with('app-success', 'Your rating has been updated.');
    }

    public function createRank()
    {
        return view('origin::lush.rank.create');
    }

    public function storeRank(StoreLushRankRequest $request)
    {
        $rank = new LushRank();
        $rank->name = $request->input('name');
        $rank->threshold = $request->input('threshold');
        $rank->save();
        return redirect()->route('settings.lush.index.ranks')->with('app-success', 'Your rank has been added.');
    }

    public function editRank(LushRank $rank)
    {
        return view('origin::lush.rank.edit')->with('rank', $rank);
    }

    public function updateRank(StoreLushRankRequest $request, LushRank $rank)
    {
        $rank->name = $request->input('name');
        $rank->threshold = $request->input('threshold');
        $rank->save();
        return redirect()->route('settings.lush.index.ranks')->with('app-success', 'Your rank has been updated.');
    }

    public function updateGroup(StoreLushGroupRequest $request, LushGroup $group)
    {
        $startsAt = Carbon::createFromFormat('m/d/Y H:i', $request->get('starts_at_date') . ' ' . $request->get('starts_at_time'));
        $endsAt = Carbon::createFromFormat('m/d/Y H:i', $request->get('ends_at_date') . ' ' . $request->get('ends_at_time'));
        $group->name = $request->input('name');
        $group->starts_at = $startsAt;
        $group->ends_at = $endsAt;
        $group->save();
        $group->lushplayers()->attach($request->input('players'));
        return redirect()->route('settings.lush.index')->with('app-success', 'Your group has been updated.');
    }

    public function players()
    {
        $lushPlayers = LushPlayer::paginate(15);
        return view('origin::lush.player.index')
            ->with('players', $lushPlayers);
    }

    public function createPlayer()
    {
        $idTypes = [
            'PP' => 'Passport',
            'DL' => 'Drivers License'
        ];
        $playerGender = [
            'M' => 'Male',
            'F' => 'Female',
            'U' => 'Prefer not to say'
        ];

        $lushRanks = LushRank::all()->pluck('name', 'id');
        $countries = Country::all()->pluck('name', 'code');
        return view('origin::lush.player.create')
            ->with('idTypes', $idTypes)
            ->with('playerGender', $playerGender)
            ->with('lushRanks', $lushRanks)
            ->with('countries', $countries);
    }

    public function storePlayer(StoreLushPlayerRequest $request)
    {
        LushPlayer::create((array) StoreLushPlayerDataTransferObject::fromArray($request->validated()));
        return redirect()->route('settings.lush.players.index')->with('app-success', 'Your player has been created.');
    }

    public function updatePlayer(LushPlayer $lushPlayer, StoreLushPlayerRequest $request)
    {
        $registered_at = Carbon::createFromFormat('m/d/Y H:i', $request->get('register_at_date') . ' ' . $request->get('register_at_time'));
        $request->merge(['registered_at' => $registered_at]);

        $lushPlayer->first_name = $request->input('first_name');
        $lushPlayer->middle_initial = $request->input('middle_initial');
        $lushPlayer->last_name = $request->input('last_name');
        $lushPlayer->birthday = Carbon::parse($request->input('birthday'));
        $lushPlayer->gender = $request->input('gender');
        $lushPlayer->lush_rank_id = $request->input('lush_rank_id');
        $lushPlayer->is_excluded = $request->input('is_excluded', false);
        $lushPlayer->registered_at = $registered_at;
        $lushPlayer->card_swipe_data = $request->input('card_swipe_data');
        $lushPlayer->card_pin = $request->input('card_pin');
        $lushPlayer->card_pin_attempts = $request->input('card_pin_attempts');
        $lushPlayer->id_type = $request->input('id_type');
        $lushPlayer->id_number = $request->input('id_number');
        $lushPlayer->id_expiration_date = Carbon::parse($request->input('id_expiration_date'));
        $lushPlayer->email = $request->input('email');
        $lushPlayer->phone = $request->input('phone');
        $lushPlayer->address = $request->input('address');
        $lushPlayer->address_2 = $request->input('address_2');
        $lushPlayer->city = $request->input('city');
        $lushPlayer->state = $request->input('state');
        $lushPlayer->zip = $request->input('zip');
        $lushPlayer->country = $request->input('country');
        $lushPlayer->phone_opt_in = $request->input('phone_opt_in');
        $lushPlayer->email_opt_in = $request->input('email_opt_in');
        $lushPlayer->save();
        return redirect()->route('settings.lush.players.index')->with('app-success', 'Your player has been updated.');
    }

    public function editPlayer(LushPlayer $lushPlayer)
    {
        $idTypes = [
            'PP' => 'Passport',
            'DL' => 'Drivers License'
        ];
        $playerGender = [
            'M' => 'Male',
            'F' => 'Female',
            'U' => 'Prefer not to say'
        ];

        $lushRanks = LushRank::all()->pluck('name', 'id');
        $countries = Country::all()->pluck('name', 'code');
        return view('origin::lush.player.edit')
            ->with('player', $lushPlayer)
            ->with('idTypes', $idTypes)
            ->with('playerGender', $playerGender)
            ->with('lushRanks', $lushRanks)
            ->with('countries', $countries);
    }

    public function deletePlayer(LushPlayer $player)
    {
        $player->lushaccounts()->forceDelete();
        $player->delete();
        return redirect()->route('settings.lush.players.index')->with('app-success', 'Your player has been deleted.');

    }

    public function deleteGroup(LushGroup $group)
    {
        $group->delete();
        return redirect()->route('settings.lush.index')->with('app-success', 'Your group has been deleted.');
    }

    public function deleteRank(LushRank $rank)
    {
        $rank->delete();
        return redirect()->route('settings.lush.index.ranks')->with('app-success', 'Your rank has been deleted.');
    }

    public function deleteRating(LushRating $rating)
    {
        $rating->delete();
        return redirect()->route('settings.lush.index.ratings')->with('app-success', 'Your rating has been deleted.');
    }

    public function removePlayerFromGroup(LushGroup $group, LushPlayer $player)
    {
        $group->lushplayers()->detach($player->id);
        return redirect()->back()->with('app-success', 'Player has been removed from the group!');
    }

}
