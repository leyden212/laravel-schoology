<?php
namespace Leyden\Schoology\Models;

class Assignment extends Model {
    protected $base_path = 'assignments';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\ReadWriteRealm::class;

    protected $guarded = [];
}