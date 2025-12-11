<?php

namespace App\Enums;

enum YearlyTurnover: string
{
    case LESS_THAN_100K = 'less_than_100k';
    case FROM_100K_TO_500K = '100k_to_500k';
    case FROM_500K_TO_1M = '500k_to_1m';
    case FROM_1M_TO_5M = '1m_to_5m';
    case OVER_5M = 'over_5m';
}
