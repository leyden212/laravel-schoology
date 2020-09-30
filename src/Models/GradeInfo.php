<?php
namespace Leyden\Schoology\Models;

class GradeInfo extends Model {
    protected $base_path = 'grades';

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
        if(isset($attributes['grades'])){
            $data = property_exists($attributes['grades'], 'grade')
                ? (array) $attributes['grades']->grade
                : $attributes['grades'] ?: [];
            $models = array_map(function($item){ return new Grade((array) $item); }, $data);

            $attributes['grades'] = new \Illuminate\Support\Collection($models);
        }

        return parent::fill($attributes);
    }
}