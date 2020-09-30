<?php
namespace Leyden\Schoology\Models;

class Enrollment extends Model {
    protected $base_path = 'enrollments';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\ListViewResource::class;

    protected $guarded = [];
}