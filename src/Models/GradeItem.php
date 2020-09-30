<?php
namespace Leyden\Schoology\Models;

class GradeItem extends Model {
    protected $base_path = 'grade_items';

    protected $is_nestable = true;

    protected $connection = \Leyden\Schoology\Resources\ListViewResource::class;

    protected $guarded = [];

    
    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        if(isset($attributes['grade_items'])){
            $data = property_exists($attributes['grade_items'], 'assignment')
                ? (array) $attributes['grade_items']->assignment
                : $attributes['grade_items'] ?: [];
            $models = array_map(function($item){ return new Assignment((array) $item); }, $data);

            $attributes['grade_items'] = new \Illuminate\Support\Collection($models);
        }

        return parent::fill($attributes);
    }
}