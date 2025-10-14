<?php

namespace App\Models;

use App\Models\Team;
use App\Models\Event;
use App\Models\Message;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // Traits
    use HasApiTokens;
    use HasFactory;
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
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

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
     * Interact with the user's first familyname1.
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
     * Interact with the user's first familyname2.
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

    public function isTeamAdmin(\App\Models\Team $team = null){
        $team = $team ?: $this->currentTeam;
        return $this->hasTeamRole($team, 'admin');
    }

    public function isInspector(){
        return $this->hasTeamRole($this->currentTeam, 'inspect');
    }

     public function meta()
    {
        return $this->hasMany(UserMeta::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->belongsToMany(Message::class, 'message_user')
            ->withPivot('read_at', 'deleted_at')
            ->withTimestamps();
    }

}
