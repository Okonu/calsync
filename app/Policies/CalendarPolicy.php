<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\User;

class CalendarPolicy
{
    public function view(User $user, Calendar $calendar): bool
    {
        return $calendar->googleAccount->user_id === $user->id;
    }

    public function update(User $user, Calendar $calendar): bool
    {
        return $calendar->googleAccount->user_id === $user->id;
    }

    public function delete(User $user, Calendar $calendar): bool
    {
        return $calendar->googleAccount->user_id === $user->id;
    }
}