<?php
namespace Leyden\Schoology\Models;

class Section extends Model
{

    protected $base_path = 'sections';
    protected $is_nestable = true; //'courses/%s/sections';

    protected $is_realm = true;

    protected $connection = \Leyden\Schoology\Resources\BulkReadWriteRealm::class;

    protected $guarded = [];

    /**
     * Nested relationships
     */

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function final_grades()
    {
        return $this->hasMany(FinalGrade::class);
    }

    public function grade_info()
    {
        return $this->hasMany(GradeInfo::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function grade_items()
    {
        return $this->hasMany(GradeItem::class);
    }

    public function grading_categories()
    {
        return $this->hasMany(GradingCategory::class);
    }

    public function grading_periods()
    {
        return $this->hasMany(GradingPeriod::class);
    }
}