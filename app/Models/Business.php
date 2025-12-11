<?php

namespace App\Models;

use App\Traits\BusinessCreditScore;
use App\Traits\HasDocuments;
use App\Traits\SendsUserNotifications;
use App\Enums\IndustryType;
use App\Enums\CompanySize;
use App\Enums\YearlyTurnover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
// use Spatie\Activitylog\LogOptions;
// use Spatie\Activitylog\Traits\LogsActivity;

class Business extends Model
{
    use SoftDeletes, HasFactory, HasDocuments, Notifiable, SendsUserNotifications, BusinessCreditScore, \App\Traits\HasUuid;
    // use LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'registration_number',
        'industry_type',
        'company_size',
        'estimated_yearly_turnover',
        'email_verified_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'industry_type' => IndustryType::class,
        'company_size' => CompanySize::class,
        'estimated_yearly_turnover' => YearlyTurnover::class,
    ];

    // Placeholder for getActivitylogOptions if we enable LogsActivity
    /*
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']);
    }
    */
}
