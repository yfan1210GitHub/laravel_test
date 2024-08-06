<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class AuthGuard extends Enum
{
    public const API = 'api';
    public const API_CUSTOMER = 'api_customer';
    public const API_ADMIN = 'api_admin';
    public const API_VENDOR = 'api_vendor';
}
