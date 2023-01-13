<?php

namespace App\Models;

use App\Events\Event\EventCreated;
use App\Events\Event\EventDeleted;
use App\Events\Event\EventUpdated;
use App\Models\Scopes\TwoYears;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'starts_at',
        'ends_at',
        'all_day',
        'time_from',
        'time_to',
    ];

    /**
     * Fields which handles like date filed
     *
     * @var array
     */
    protected $dates = [
        'starts_at',
        'ends_at',
    ];
    
    /*
    protected $dispatchesEvents = [
        'created' => EventCreated::class,
        'updated' => EventUpdated::class,
        'deleted' => EventDeleted::class,
    ];
    */
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new TwoYears);
    }

    /**
     * Event to users relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('permission', 'status')
            ->using(EventUser::class)
            ->withTimestamps();
    }

    /**
     * Event users ids
     *
     * @return array
     */
    public function usersIds()
    {
        return $this->users()
            ->pluck('user_id')
            ->all();
    }

    /**
     * Event editors ids
     *
     * @return array
     */
    public function editorsIds()
    {
        return $this->users()
            ->where('permission', '=', EventUser::PERMISSION_EDIT)
            ->pluck('user_id')
            ->all();
    }

    /**
     * Event owners ids
     *
     * @return array
     */
    public function ownersIds()
    {
        return $this->users()
            ->where('permission', '=', EventUser::PERMISSION_OWN)
            ->pluck('user_id')
            ->all();
    }

    /**
     * Event maybe users relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function maybeUsers()
    {
        return $this->users()
            ->where('status', '=', EventUser::STATUS_MAYBE);
    }

    /**
     * Event going users relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function goingUsers()
    {
        return $this->users()
            ->where('status', '=', EventUser::STATUS_GOING);
    }

    /**
     * Event not going users relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notGoingUsers()
    {
        return $this->users()
            ->where('status', '=', EventUser::STATUS_NOT_GOING);
    }

    /**
     * User Event permission title
     *
     * @return array
     */
    public function permissionTitle()
    {
        return EventUser::permissionTitle($this->users->first()->pivot->permission);
    }

    /**
     * User Event status title
     *
     * @return array
     */
    public function statusTitle()
    {
        return EventUser::statusTitle($this->users()->first()->pivot->status);
    }
}
