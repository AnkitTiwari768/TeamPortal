<?php

declare(strict_types=1);

namespace App\Domain\UserLog;

enum UserLogType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case RoleChanged = 'role_changed';
    case PasswordChanged = 'password_changed';
    case Activated = 'activated';
    case Deactivated = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'User Created',
            self::Updated => 'User Updated',
            self::RoleChanged => 'Role Changed',
            self::PasswordChanged => 'Password Changed',
            self::Activated => 'Activated',
            self::Deactivated => 'Deactivated',
        };
    }

    /**
     * @return array<string,string> value => label, for building filter dropdowns
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}
