<?php
namespace Leyden\Schoology\Models;

class Course extends Model {

    protected $base_path = 'courses';

    protected $connection = \Leyden\Schoology\Resources\BulkReadWriteRealm::class;

    protected $guarded = [];

    /**
     * Relations
     */
    public function sections() {
        return $this->hasMany(Section::class);
    }
}