<?php

namespace TaskLedger\App\Models;

use TaskLedger\App\Models\Model;
use TaskLedger\Framework\Database\Orm\UserProxyTrait;

class User extends Model
{
    use UserProxyTrait;

    public $timestamps = false;

    protected $table = 'users';
    
    protected $primaryKey = 'ID';

    public function posts()
    {
        return $this->hasMany(Post::class, 'post_author', 'ID');
    }
}
