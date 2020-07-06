<?php

namespace App\Models;

use App\Models\Base\IResource;
use App\Models\Base\IUser;
use App\Models\Base\IUserHasSettings;
use App\ModelTraits\OnlyAttributesToArrayTrait;
use App\ModelTraits\MemorizeTrait;
use App\ModelTraits\PassportTrait;
use App\ModelTraits\ResourceTrait;
use App\Notifications\ResetPasswordNotification;
use App\Utils\ConfigHelper;
use App\Utils\ClientSettings\Facade;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 * @package App\Models
 * @property int $id
 * @property string $display_name
 * @property string $email
 * @property PasswordReset $passwordReset
 */
class User extends Authenticatable implements HasLocalePreference, IUser, IResource
{
    use OnlyAttributesToArrayTrait, PassportTrait, HasApiTokens, Notifiable, MemorizeTrait, ResourceTrait, SoftDeletes;

    const PROTECTED = [1, 2];

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
    ];

    protected $visible = [
        'id',
        'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getSdStCreatedAtAttribute()
    {
        return Facade::dateTimer()->compound(
            'shortDate',
            ' ',
            'shortTime',
            $this->attributes['created_at']
        );
    }

    public function getSdStUpdatedAtAttribute()
    {
        return Facade::dateTimer()->compound(
            'shortDate',
            ' ',
            'shortTime',
            $this->attributes['updated_at']
        );
    }

    public function scopeNoneProtected($query)
    {
        return $query->whereNotIn('id', static::PROTECTED);
    }

    #region Relationship
    public function passwordReset()
    {
        return $this->hasOne(PasswordReset::class, 'email', 'email');
    }
    #endregion

    #region CanResetPassword
    public function sendPasswordResetNotification($token, $fromUser = null)
    {
        $this->notify(new ResetPasswordNotification($token, $fromUser));
    }
    #endregion

    #region HasLocalePreference
    public function preferredLocale()
    {
        return $this->preferredSettings()->getLocale();
    }

    #endregion

    public function preferredEmail()
    {
        return $this->email;
    }

    public function preferredName()
    {
        return Str::before($this->preferredEmail(), '@');
    }

    public function preferredAvatarUrl()
    {
        return ConfigHelper::defaultAvatarUrl();
    }

    public function preferredSettings()
    {
        return $this instanceof IUserHasSettings ? $this->getSettings() : Facade::capture();
    }

    public function getPasswordResetExpiredAt()
    {
        $passwordReset = $this->passwordReset;
        return empty($passwordReset) ? null : $passwordReset->sdStExpiredAt;
    }
}