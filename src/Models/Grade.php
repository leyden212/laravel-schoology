<?php
namespace Leyden\Schoology\Models;

class Grade extends Model {
    protected $base_path = 'grades';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\ListViewResource::class;

    protected $guarded = [];
}