<?php
namespace Leyden\Schoology\Models;

class User extends Model
{

    protected $base_path = 'users';

    protected $connection = \Leyden\Schoology\Resources\BulkReadWriteRealm::class;

    protected $is_realm = true;

    protected $guarded = [];

    /**
     * Nested relationships
     */

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

}