<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Event;
use App\Models\Message;
use App\Models\Team;
use App\Traits\HasNotificationPreferences;
use App\Traits\HasPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Represents a user of the CTH application with authentication,
 * team membership, time tracking, and permission management.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $user_code Auto-generated user code
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property bool $is_admin Global administrator flag
 * @property bool $geolocation_tracking Enable GPS tracking
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read Team|null $currentTeam
 * @property-read \Illuminate\Database\Eloquent\Collection<Team> $teams
 * @property-read \Illuminate\Database\Eloquent\Collection<Team> $ownedTeams
 * @property-read \Illuminate\Database\Eloquent\Collection<Event> $events
 * @property-read \Illuminate\Database\Eloquent\Collection<Message> $messages
 * @property-read \Illuminate\Database\Eloquent\Collection<UserMeta> $meta
 * @property-read \Illuminate\Database\Eloquent\Collection<Permission> $permissions
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class User extends Authenticatable
{
    // Traits
    use HasApiTokens;
    use HasFactory;
    use HasNotificationPreferences;
    use HasPermissions;
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

        static::creating(function ($user) {
            // Auto-generate user_code if not provided
            if (empty($user->user_code)) {
                $user->user_code = 'USR' . str_pad(User::max('id') + 1, 6, '0', STR_PAD_LEFT);
            }
        });

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
        'vacation_calculation_type',
        'vacation_working_days',
        'geolocation_enabled',
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
        'vacation_calculation_type' => 'string',
        'vacation_working_days' => 'integer',
        'geolocation_enabled' => 'boolean',
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

    /**
     * Get the dashboard widget order and visibility for this user.
     * Returns the saved preferences or the default if not set.
     *
     * @return array
     */
    public function getDashboardWidgetOrder(): array
    {
        $defaultPreferences = [
            'order' => [
                'announcements',
                'inbox-summary',
                'sent-messages-summary',
                'stats-cards',
                'latest-clock-ins',
            ],
            'hidden' => [], // No widgets hidden by default
        ];

        $meta = $this->meta()->where('meta_key', 'dashboard_widget_order')->first();

        if (!$meta) {
            return $defaultPreferences;
        }

        $preferences = json_decode($meta->meta_value, true);

        // Ensure the structure is valid
        if (!is_array($preferences) || !isset($preferences['order'])) {
            return $defaultPreferences;
        }

        // Ensure hidden array exists
        if (!isset($preferences['hidden'])) {
            $preferences['hidden'] = [];
        }

        // Merge missing default widgets into user preferences
        // This ensures new widgets appear for existing users
        $missingWidgets = array_diff($defaultPreferences['order'], $preferences['order']);
        if (!empty($missingWidgets)) {
            $preferences['order'] = array_merge($preferences['order'], $missingWidgets);
        }

        return $preferences;
    }

    /**
     * Set the dashboard widget order and visibility for this user.
     *
     * @param array $preferences
     * @return void
     */
    public function setDashboardWidgetOrder(array $preferences): void
    {
        $this->meta()->updateOrCreate(
            ['meta_key' => 'dashboard_widget_order'],
            ['meta_value' => json_encode($preferences)]
        );
    }

    /**
     * Get the vacation days limit based on user's preference.
     *
     * @return int
     */
    public function getVacationDaysLimit(): int
    {
        if ($this->vacation_calculation_type === 'working') {
            return $this->vacation_working_days ?? 22;
        }
        
        return 30; // Natural days default
    }

    /**
     * Check if user can perform an action (granular permission system).
     * 
     * @param string $permission Permission name (e.g., 'events.create.team')
     * @param Team|int|null $team Team context
     * @param array $additionalContext Additional context data
     * @return bool
     */
    public function can($permission, $team = null, array $additionalContext = []): bool
    {
        $context = $additionalContext;
        
        if ($team) {
            $context['team_id'] = $team instanceof Team ? $team->id : $team;
        } elseif ($this->currentTeam) {
            $context['team_id'] = $this->currentTeam->id;
        }

        return $this->hasPermission($permission, $context);
    }

    /**
     * Check if user cannot perform an action.
     */
    public function cannot($permission, $team = null, array $additionalContext = []): bool
    {
        return !$this->can($permission, $team, $additionalContext);
    }

    /**
     * Check if user has ANY of the given permissions.
     */
    public function hasAnyPermission(array $permissions, $team = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission, $team)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has ALL of the given permissions.
     */
    public function hasAllPermissions(array $permissions, $team = null): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can($permission, $team)) {
                return false;
            }
        }
        return true;
    }
}
