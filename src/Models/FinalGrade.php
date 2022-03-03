<?php
namespace Leyden\Schoology\Models;

class FinalGrade extends Model
{
    protected $base_path = 'grades';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\ListViewResource::class;

    protected $guarded = [];

// protected $altResourceName = 'grades.final_grade';

}