<?php

namespace TaskLedger\App\Models;

use TaskLedger\App\Models\Model;

class Meta extends Model
{
    protected $table = 'task_ledger_meta';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public static function getMeta($objectType, $objectId, $metaKey)
    {
        return self::where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->where('meta_key', $metaKey)
            ->first();
    }

    public static function getMetaForTask($taskId, $metaKey)
    {
        return self::getMeta('task', $taskId, $metaKey);
    }

    public static function getMetaForBoard($boardId, $metaKey)
    {
        return self::getMeta('board', $boardId, $metaKey);
    }

    public static function setMeta($objectType, $objectId, $metaKey, $metaValue)
    {
        $metaValue = maybe_serialize($metaValue);
        $meta = self::getMeta($objectType, $objectId, $metaKey);
        if ($meta) {
            $meta->meta_value = $metaValue;
            $meta->save();
        } else {
            $meta = new self();
            $meta->object_type = $objectType;
            $meta->object_id = $objectId;
            $meta->meta_key = $metaKey;
            $meta->meta_value = $metaValue;
            $meta->save();
        }
        return $meta;
    }

    public static function setMetaForTask($taskId, $metaKey, $metaValue)
    {
        return self::setMeta('task', $taskId, $metaKey, $metaValue);
    }

    public static function setMetaForBoard($boardId, $metaKey, $metaValue)
    {
        return self::setMeta('board', $boardId, $metaKey, $metaValue);
    }

    public static function deleteMeta($objectType, $objectId, $metaKey)
    {
        return self::where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->where('meta_key', $metaKey)
            ->delete();
    }

    public static function deleteMetaForTask($taskId, $metaKey)
    {
        return self::deleteMeta('task', $taskId, $metaKey);
    }

    public static function deleteMetaForBoard($boardId, $metaKey)
    {
        return self::deleteMeta('board', $boardId, $metaKey);
    }

    public function getMetaValueAttribute($value)
    {
        return maybe_unserialize($value);
    }
}
