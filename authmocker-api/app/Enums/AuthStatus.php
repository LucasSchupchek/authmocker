<?php

namespace App\Enums;

enum AuthStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
}
