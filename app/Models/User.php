<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'avatar',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * All User events relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function events()
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('permission', 'status')
            ->using(EventUser::class)
            ->withTimestamps();
    }

    /**
     * User may edit events ids
     *
     * @return array
     */
    public function editableEventsIds()
    {
        return $this->events()
            ->where('permission', '=', EventUser::PERMISSION_EDIT)
            ->pluck('event_id')
            ->all();
    }

    /**
     * User own events ids
     *
     * @return array
     */
    public function ownerEventsIds()
    {
        return $this->events()
        ->where('permission', '=', EventUser::PERMISSION_OWN)
        ->pluck('event_id')
        ->all();
    }

    /**
     * User Event permission title
     *
     * @return array
     */
    public function permissionTitle()
    {
        return EventUser::permissionTitle($this->pivot->permission);
    }

    /**
     * User Event status title
     *
     * @return array
     */
    public function statusTitle()
    {
        return EventUser::statusTitle($this->pivot->status);
    }

    /**
     * User maybe events relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function maybeEvents()
    {
        return $this->events()
            ->where('status', '=', EventUser::STATUS_MAYBE);
    }

    /**
     * User going events relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function goingEvents()
    {
        return $this->events()
            ->where('status', '=', EventUser::STATUS_GOING);
    }

    /**
     * User not going events relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notGoingEvents()
    {
        return $this->events()
            ->where('status', '=', EventUser::STATUS_NOT_GOING);
    }

    /**
     * Get user full name
     *
     * @return string
     */
    public function fullName()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Save avatar file
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null $file
     * 
     * @return string
     */
    public function saveAvatar($file)
    {
        $file->store('avatars/'.$this->id, 'public');
        return $file->hashName();
    }

    /**
     * Avatar location in public storage
     *
     * @return string
     */
    public function avatarPath()
    {
        return 'avatars/'.$this->id.'/'.$this->getOriginal('avatar');
    }

    /**
     * Get avatar url
     *
     * @return string
     */
    public function avatarUrl()
    {
        $path = $this->avatarPath();
        
        if (!Storage::disk('public')->exists($path)) {
            $path = 'avatars/dummy.jpg';
        }

        return Storage::url($path);
    }

    /**
     * Delete avatar file
     *
     * @return void
     */
    public function deleteAvatar()
    {
        $path = $this->avatarPath();

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
