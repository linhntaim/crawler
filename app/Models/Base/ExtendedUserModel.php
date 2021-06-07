<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Models\Base;

use App\Models\DatabaseNotification;
use App\Models\User;
use App\ModelTraits\UserTrait;

/**
 * Class ExtendedUserModel
 * @package App\Models\Base
 * @property int $user_id
 * @property string $username
 * @property string $email
 * @property User $user
 * @property DatabaseNotification[] $notifications
 */
abstract class ExtendedUserModel extends Model implements IUser
{
    use UserTrait {
        modelConstruct as userConstruct;
    }

    public const PROTECTED = User::PROTECTED;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        $this->userConstruct();

        parent::__construct($attributes);
    }

    public static function getProtectedKey()
    {
        return 'user_id';
    }

    public function preferredName()
    {
        return $this->user->preferredName();
    }

    public function preferredEmail()
    {
        return $this->user->preferredEmail();
    }

    public function preferredAvatarUrl()
    {
        return $this->user->preferredAvatarUrl();
    }

    public function preferredSettings()
    {
        return $this->user->preferredSettings();
    }

    public function preferredLocale()
    {
        return $this->user->preferredLocale();
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function getPasswordResetExpiredAt()
    {
        return $this->user->getPasswordResetExpiredAt();
    }

    public function getUsernameAttribute()
    {
        return $this->user->username;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
