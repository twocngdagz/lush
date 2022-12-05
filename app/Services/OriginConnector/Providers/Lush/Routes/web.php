<?php

Route::group(['middleware' => ['web', 'auth', 'set-tenant', 'password_expired']], function () {

    Route::group([
        'prefix' => 'settings',
        'as' => 'settings.',
        'namespace' => 'Settings',
        'middleware' => 'can:view-settings'
    ], function () {
        Route::group([
            'prefix' => 'lush',
            'as' => 'lush.',
            'namespace' => '\App\Services\OriginConnector\Providers\Lush\Controllers',
            'middleware' => 'lush_cms'
        ], function () {
            Route::get('/players', ['as' => 'players.index', 'uses' => 'SettingsController@players']);
            Route::get('/', ['as' => 'index', 'uses' => 'SettingsController@index']);
            Route::get('/#ranks', ['as' => 'index.ranks', 'uses' => 'SettingsController@index']);
            Route::get('/#ratings', ['as' => 'index.ratings', 'uses' => 'SettingsController@index']);
            Route::get('/ratings/create', ['as' => 'ratings.create', 'uses' => 'SettingsController@createRating']);
            Route::post('/ratings/store', ['as' => 'ratings.store', 'uses' => 'SettingsController@storeRating']);
            Route::get('/ratings/{rating}/edit', ['as' => 'ratings.edit', 'uses' => 'SettingsController@editRating']);
            Route::get('/ratings/{rating}/delete', ['as' => 'ratings.delete', 'uses' => 'SettingsController@deleteRating']);
            Route::post('/ratings/{rating}/update', ['as' => 'ratings.update', 'uses' => 'SettingsController@updateRating']);
            Route::get('/groups/create', ['as' => 'groups.create', 'uses' => 'SettingsController@createGroup']);
            Route::get('/ranks/create', ['as' => 'ranks.create', 'uses' => 'SettingsController@createRank']);
            Route::post('/ranks/store', ['as' => 'ranks.store', 'uses' => 'SettingsController@storeRank']);
            Route::get('/ranks/{rank}/edit', ['as' => 'ranks.edit', 'uses' => 'SettingsController@editRank']);
            Route::get('/ranks/{rank}/delete', ['as' => 'ranks.delete', 'uses' => 'SettingsController@deleteRank']);
            Route::post('/ranks/{rank}/update', ['as' => 'ranks.update', 'uses' => 'SettingsController@updateRank']);
            Route::post('/groups/store', ['as' => 'groups.store', 'uses' => 'SettingsController@storeGroup']);
            Route::get('/groups/{group}/edit', ['as' => 'groups.edit', 'uses' => 'SettingsController@editGroup']);
            Route::post('/groups/{group}/update', ['as' => 'groups.update', 'uses' => 'SettingsController@updateGroup']);
            Route::get('/groups/{group}/delete', ['as' => 'groups.delete', 'uses' => 'SettingsController@deleteGroup']);
            Route::get('/groups/{group}/players/{player}/remove', ['as' => 'groups.player.remove', 'uses' => 'SettingsController@removePlayerFromGroup']);
            Route::get('/players/create', ['as' => 'players.create', 'uses' => 'SettingsController@createPlayer']);
            Route::post('/players/store', ['as' => 'players.store', 'uses' => 'SettingsController@storePlayer']);
            Route::post('players/{lushPlayer}/update', ['as' => 'players.update', 'uses' => 'SettingsController@updatePlayer']);
            Route::get('players/{lushPlayer}/edit', ['as' => 'players.edit', 'uses' => 'SettingsController@editPlayer']);
            Route::get('players/{player}/delete', ['as' => 'players.delete', 'uses' => 'SettingsController@deletePlayer']);
        });
    });
});
