<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountStatus;
use App\Enums\AuthGuard;
use App\Enums\VerifyTokenType;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as SpatieAuthenticatable;
use App\Notifications\Emails\ResetPasswordNotification;
use App\Notifications\Emails\FirstTimeLoginNotification;
use Str;

class Authenticatable extends SpatieAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_dial_code',
        'mobile',
        'bonuslink_no',
        'bonuslink_pin',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('guardName', function ($builder) {
            $builder->whereHas('roles', function($builder) {
                return $builder->where('guard_name', (new static)->guard_name);
            });
        });

        static::creating(function (Authenticatable $user) {
            if(empty($user->password)) {
                // $user->password = Hash::make(str_random(8));
                $user->password = '$2y$10$AUIUwU5UdrRzBnEScfvVtuH0vIZ7qjFjUiK4JlQiVYmO36edil9qy'; // Qwer1234&
            }
            if(empty($user->status)) {
                $user->status = AccountStatus::defaultAccountStatus();
            }
        });
        static::created(function(Authenticatable $user) {
            $user->refresh();
            //$user->sendFirstTimeLoginNotification();
        });
    }

    # RELATIONSHIP
    public function failedLogins()
    {
        return $this->hasMany(FailedLogin::class, 'email', 'email');
    }

    public function verifyTokens()
    {
        return $this->hasMany(VerifyToken::class, 'email', 'email');
    }

    public function getSingleRole()
    {
        $this->loadMissing('roles');

        return $this->roles->first();
    }

    # SCOPE
    public function scopeActive($query)
    {
        return $query->whereIn('status', AccountStatus::getActiveStatuses());
    }

    # FUNCTION
    public function isActive()
    {
        return in_array($this->status, AccountStatus::getActiveStatuses());
    }

    # NOTIFICATION ROUTE
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    public function getResetPasswordUrl($token)
    {
        return sprintf(config('app.redirect_link.reset_password') . '?token=%s&email=%s', $token, $this->email);
    }

    public function getFirstTimeLoginUrl($token)
    {
        return sprintf(config('app.redirect_link.first_time_login') . '?token=%s&email=%s', $token, $this->email);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($this->getResetPasswordUrl($token)));
    }

    public function sendFirstTimeLoginNotification()
    {
        if(!$this->is_first_time_login) {
            return;
        }
        
        # Send first time login notification
        $plaintext_token = Str::random(32);
        $this->verifyTokens()->create([
            'token' => Hash::make($plaintext_token),
            'type' => VerifyTokenType::FIRST_TIME_LOGIN,
            'expires_at' => now()->addSeconds(config('app.verify_token_timeout')),
        ]);

        $this->notify(new FirstTimeLoginNotification($this->getFirstTimeLoginUrl($plaintext_token)));
    }
}
