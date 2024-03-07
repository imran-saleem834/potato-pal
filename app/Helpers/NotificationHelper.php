<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    public static function addedAction(string $model, int $id)
    {
        $name = auth()->user()->name;
        static::create('Added', "$model with id $id added by $name");
    }

    public static function duplicatedAction(string $model, int $id)
    {
        $name = auth()->user()->name;
        static::create('Duplicated', "$model with id $id duplicated by $name");
    }

    public static function updatedAction(string $model, int $id)
    {
        $name = auth()->user()->name;
        static::create('Updated', "$model with id $id updated by $name");
    }

    public static function deleteAction(string $model, int $id)
    {
        $name = auth()->user()->name;
        static::create('Deleted', "$model with id $id deleted by $name");
    }

    public static function create(string $action, string $notification)
    {
        Notification::create([
            'action'       => $action,
            'notification' => $notification,
        ]);
    }
}
