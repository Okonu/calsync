<?php

namespace App\Enums;

enum IndustryType: string
{
    case TECHNOLOGY = 'technology';
    case FINANCE = 'finance';
    case HEALTHCARE = 'healthcare';
    case RETAIL = 'retail';
    case MANUFACTURING = 'manufacturing';
    case OTHER = 'other';
}
