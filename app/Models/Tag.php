<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'color'];

    /***
     * One Tag have multiple tasks
    */

    public function tasks(): belongstoMany
    {
        return $this->belongsToMany(Task::class, 'tag_task')->withTimestamps();
    }
}
