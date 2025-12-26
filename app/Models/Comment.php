<?php

namespace TaskLedger\App\Models;

use TaskLedger\App\Models\Model;

class Comment extends Model
{
    const CREATED_AT = 'comment_date';
    
    const UPDATED_AT = null;

    protected $table = 'comments';
    
    protected $primaryKey = 'comment_ID';
}
