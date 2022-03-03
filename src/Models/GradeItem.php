<?php
namespace Leyden\Schoology\Models;

class GradeItem extends Model
{
    protected $base_path = 'grade_items';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\ListViewResource::class;

    protected $guarded = [];

    protected $altResourceName = 'assignment';

}