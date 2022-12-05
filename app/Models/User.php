<?php

namespace App\Models;

use App\Traits\LogsAllUserActivity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;

use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRolesAndAbilities;
    use Notifiable;
    use LogsAllUserActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_changed_at',
        'is_locked'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    protected $dates = ['last_login', 'created_at', 'updated_at'];
    protected $casts = ['is_active' => 'boolean', 'is_locked' => 'boolean'];

    /**
     * Hard-coded role levels array for use in 'canManage' scope & method
     *
     * We already have a levels column in roles but it is being depreciated, so it's better if define it ourselves.
     *
     * @var array
     */
    private $role_levels = [
        'super-admin' => 5,
        'account-admin' => 4,
        'marketing-director' => 3,
        'marketing-manager' => 2,
        'player-rep' => 1,
    ];

    /**
     * Account relationship for users
     *
     * @return BelongsTo
     **/
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope method to filter by property
     **/
    public function scopeForProperties($query): ?Builder
    {
        if (!auth()->user() || auth()->user()->isA('super-admin')) {
            return null;
        }

        $properties = auth()->user()->property_ids;
        return $query->whereHas('properties', function ($query) use ($properties) {
            $query->whereIn('id', $properties);
        });
    }

    /**
     * Scope method to filter roles that i can manage
     **/
    public function scopeCanManage($query): ?Builder
    {
        if (!auth()->user()) {
            return null;
        }

        // because the heirarchy isn't define in DB we will hard-code it here..
        $myRole = auth()->user()->roles()->first()->name;
        if ($myRole == 'super-admin') {
            return null;
        }

        $myLevel = $this->role_levels[$myRole];

        // build list of role names that i can manage
        $can_manage_roles = [];
        foreach ($this->role_levels as $roleName => $level) {
            if ($myLevel >= $level) {
                $can_manage_roles[] = $roleName;
            }
        }

        // filter users by roles i can manage
        return $query->whereHas('roles', function ($query) use ($can_manage_roles) {
            $query->whereIn('name', $can_manage_roles);
        });
    }

    /**
     * Property Relationship
     * @return BelongsToMany
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'user_property')->withTimestamps();
    }

    public function getDefaultPropertyAttribute(): Property
    {
        if ($this->property_id) {
            return $this->belongsTo(Property::class, 'property_id')->first();
        }

        return $this->properties()->first();
    }

    public function getPropertyIdsAttribute(): array
    {
        return $this->properties->pluck('id')->toArray();
    }

    public function getPropertyCodesAttribute(): string
    {
        return implode(', ', $this->properties()->pluck('property_code')->toArray());
    }

    public function getPreviousLoginAttribute(): string
    {
        return session('last_login') ? Carbon::parse(session('last_login'))->diffForHumans() : 'First Login';
    }

    /**
     * Quick role level check to see if we can edit this user
     **/
    public function getCanEditAttribute(): bool
    {
        if (!auth()->user()) {
            return false;
        }

        $myRole = auth()->user()->roles()->first()->name;
        if ($myRole == 'super-admin') {
            return true;
        }

        $myLevel = $this->role_levels[$myRole];

        $userRole = $this->roles()->first()->name;
        $userLevel = $this->role_levels[$userRole];

        return ($myLevel >= $userLevel);
    }

    public function generateAccessToken(): string
    {
        return Str::random(60);
    }

    /**
     * Password history relationship for users
     * @return HasMany
     **/
    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }
}
