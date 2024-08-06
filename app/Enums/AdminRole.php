<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class AdminRole extends Enum
{
    public const SUPER_ADMIN = 'Super Admin';
    public const ADMIN = 'Admin';
}
