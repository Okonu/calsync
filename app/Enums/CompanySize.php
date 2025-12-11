<?php

namespace App\Enums;

enum CompanySize: string
{
    case MICRO = 'micro'; // 1-10
    case SMALL = 'small'; // 11-50
    case MEDIUM = 'medium'; // 51-200
    case LARGE = 'large'; // 201-500
    case ENTERPRISE = 'enterprise'; // 500+
}
