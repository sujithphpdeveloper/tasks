<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskLog extends Model
{
    /** @use HasFactory<\Database\Factories\TaskLogFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['taks_id', 'tag_id', 'operation_type', 'changes'];

    /***
     * One Log will be related to one Task
    */
    public function task(): belongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /***
     * Each log will be associated with a Single User/Admin
    */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
