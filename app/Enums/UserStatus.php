<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Restricted = 'restricted';
    case Disabled = 'disabled';
}
