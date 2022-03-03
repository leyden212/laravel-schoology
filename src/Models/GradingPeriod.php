<?php
namespace Leyden\Schoology\Models;

class GradingPeriod extends Model
{
    protected $base_path = 'grading_periods';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\ListViewResource::class;

    protected $guarded = [];
}