<?php

namespace App\Models;

use App\Models\Event;
use App\Models\Message;
use App\Models\Team;
use App\Traits\HasNotificationPreferences;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

/**
 * Represents a user of the application.
 *
 * This model contains all the information related to a user, including their
 * personal information, authentication details, and relationships to other
 * models.
 */
class User extends Authenticatable
{
    // Traits
    use HasApiTokens;
    use HasFactory;
    use HasNotificationPreferences;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::boot();

        static::created(function ($user) {
            $user->meta()->create([
                'meta_key' => 'schedule',
                'meta_value' => '[{"start":"09:00","end":"14:00","days":["L","X","V"]},{"start":"16:00","end":"19:00","days":["L","M","X","J","V"]}]'
            ]);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_code',
        'name',
        'family_name1',
        'family_name2',
        'email',
        'password',
        'week_starts_on',
        'is_admin',
        'max_owned_teams',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'max_owned_teams' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the events for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Interact with the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords($value),
            set: fn ($value) => ucwords(strtolower($value)),
        );
    }

    /**
     *
     * Interact with the user's first family name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function familyName1(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords($value),
            set: fn ($value) => ucwords(strtolower($value)),
        );
    }

    /**
     * Interact with the user's second family name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function familyName2(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords($value),
            set: fn ($value) => ucwords(strtolower($value)),
        );
    }

    /**
     * Get all of the teams the user owns or belongs to.
     * Global administrators can see all teams.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allTeams()
    {
        // If the user is a global administrator, return all teams
        if ($this->is_admin) {
            return Team::all();
        }

        // Otherwise, use the default behavior from HasTeams trait
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Switch the user's context to the given team.
     * Global administrators can switch to any team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function switchTeam($team)
    {
        // Allow global administrators to switch to any team
        if ($this->is_admin) {
            $this->forceFill([
                'current_team_id' => $team->id,
            ])->save();

            $this->setRelation('currentTeam', $team);

            return true;
        }

        // Otherwise, use the default behavior from HasTeams trait
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->forceFill([
            'current_team_id' => $team->id,
        ])->save();

        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * Check if the user is an administrator of a given team.
     *
     * @param \App\Models\Team|null $team
     * @return boolean
     */
    public function isTeamAdmin(?\App\Models\Team $team = null): bool
    {
        $team = $team ?: $this->currentTeam;
        if (!$team) {
            return false;
        }
        return $this->hasTeamRole($team, 'admin');
    }

    /**
     * Check if the user has the inspector role in the current team.
     *
     * @return boolean
     */
    public function isInspector(): bool
    {
        if (!$this->currentTeam) {
            return false;
        }
        return $this->hasTeamRole($this->currentTeam, 'inspect');
    }

    /**
     * Get the metadata associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany(UserMeta::class);
    }

    /**
     * Get the messages sent by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the messages received by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function receivedMessages()
    {
        return $this->belongsToMany(Message::class, 'message_user')
            ->withPivot('read_at', 'deleted_at')
            ->withTimestamps();
    }

    /**
     * Check if the user can create more teams.
     *
     * @return boolean
     */
    public function canCreateTeam(): bool
    {
        // Prevent users in the Welcome Team from creating teams
        if ($this->currentTeam && $this->currentTeam->name === Team::WELCOME_TEAM_NAME) {
            return false;
        }

        // Global admins can create unlimited teams
        if ($this->is_admin) {
            return true;
        }

        // If max_owned_teams is null, user has unlimited teams
        if ($this->max_owned_teams === null) {
            return true;
        }

        // Check current owned teams count
        return $this->ownedTeams()->count() < $this->max_owned_teams;
    }

    /**
     * Get the number of remaining team slots available.
     *
     * @return int|null Returns null if unlimited
     */
    public function getRemainingTeamSlots(): ?int
    {
        // Global admins have unlimited slots
        if ($this->is_admin) {
            return null;
        }

        // If max_owned_teams is null, unlimited
        if ($this->max_owned_teams === null) {
            return null;
        }

        $currentCount = $this->ownedTeams()->count();
        return max(0, $this->max_owned_teams - $currentCount);
    }

    /**
     * Transfer this user to another team.
     * Removes from current team memberships and adds to target team.
     * Migrates user's events to the target team.
     *
     * @param \App\Models\Team $targetTeam
     * @param string|null $role
     * @return void
     */
    public function transferToTeam(Team $targetTeam, ?string $role = null): void
    {
        // Cannot transfer if user owns the target team
        if ($targetTeam->user_id === $this->id) {
            throw new \InvalidArgumentException('Cannot transfer user to a team they own.');
        }

        // Remove from all current team memberships (not owned teams)
        $this->teams()->detach();

        // Add to target team
        $targetTeam->users()->attach($this->id, ['role' => $role]);

        // Update current team
        $this->forceFill([
            'current_team_id' => $targetTeam->id,
        ])->save();

        // Migrate user's events to the target team
        $this->events()->update(['team_id' => $targetTeam->id]);
    }
}
