<?php
namespace Leyden\Schoology\Models;

class GradingCategory extends Model {
    protected $base_path = 'grading_categories';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\GradingCategoryResource::class;

    protected $guarded = [];
}