<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventUser extends Pivot
{
    const STATUS_DUMMY = 99;
    const STATUS_PENDING = 0;
    const STATUS_NOT_GOING = 1;
    const STATUS_MAYBE = 2;
    const STATUS_GOING = 3;
    
    const PERMISSION_DUMMY = 99;
    const PERMISSION_OWN = 0;
    const PERMISSION_VIEW = 1;
    const PERMISSION_EDIT = 2;
    
    protected static $permissionsTitles = [
        self::PERMISSION_OWN => 'Owner',
        self::PERMISSION_VIEW => 'View only',
        self::PERMISSION_EDIT => 'Can edit',
    ];

    protected static $statusesTitles = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_NOT_GOING => 'Not going',
        self::STATUS_MAYBE => 'Maybe',
        self::STATUS_GOING => 'Going',
    ];
    
    /**
     * Permission title
     *
     * @param integer $permission
     * @return string
     */
    public static function permissionTitle(int $permission)
    {
        return self::$permissionsTitles[$permission];
    }

    /**
     * Status title
     *
     * @param integer $status
     * @return string
     */
    public static function statusTitle(int $status)
    {
        return self::$statusesTitles[$status];
    }
    /**
     * Permissions
     *
     * @param integer $permission
     * @return string
     */
    public static function permissions()
    {
        return self::$permissionsTitles;
    }

    /**
     * Statuses
     *
     * @param integer $status
     * @return string
     */
    public static function statuses()
    {
        return self::$statusesTitles;
    }
}
