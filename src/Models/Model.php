<?php

namespace Leyden\Schoology\Models;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Leyden\Schoology\Exceptions\PathRequiresParentParameters;

abstract class Model {
    use HasAttributes, HidesAttributes, GuardsAttributes, HasTimestamps;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    CONST CREATED_AT = null;
    
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    CONST UPDATED_AT = null;
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';
    
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
    

    protected $connection = \Leyden\Schoology\Resources\Base::class;
    protected $_resource = null;
    protected $base_path = '';
    protected $is_nestable = false;
    protected $path_parents = [];


    /**
     * Create a new model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        // $this->bootIfNotBooted();

        // $this->initializeTraits();

        //$this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Calculate resource path
     */
    public function getBasePath() {
        return $this->base_path;
    }
    
    public function getNestedPath($require_parents = true){
        if ($this->is_nestable && ($require_parents && !$this->path_parents)) {
            throw new PathRequiresParentParameters();
        }
        return $this->is_nestable
            ? implode('/', $this->path_parents).'/'.$this->getBasePath()
            : $this->getBasePath();
    }

    public function setParentPath(array $parents){
        $this->path_parents = array_filter($parents);
        return $this;
    }

    public function hasMany($child_class, array $parent_base_path = [], $forceNesting = true){
        if(!$parent_base_path) {
            $parent_base_path = [
                $this->getNestedPath(false),
                $this->getKey(),
            ];
        }
        return (new $child_class())->setParentPath($parent_base_path);
    }

    public function resource() {
        if (!$this->_resource) { $this->_resource = new $this->connection($this); }
        return $this->_resource;
    }

    public static function find($id) {
        return (new static)->resource()->find($id);
    }
    
    public function get() {
        return $this->resource()->get();
    }
    
    public static function all() {
        return (new static)->resource()->all();
    }
    
    
    public function where($field, $value) {
        return $this->resource()
            ->where($field, $value);
    }
    
    public static function paginate($start, $count) {
        return (new static)->resource()
            ->paginate($start, $count);
    }

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
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new \Illuminate\Database\Eloquent\MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }
        }

        return $this;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return $this->keyType;
    }
}