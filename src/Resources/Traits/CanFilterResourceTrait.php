<?php

namespace Leyden\Schoology\Resources\Traits;

trait CanFilterResourceTrait
{
    private $wheres = [];

    protected function resetWheres()
    {
        $this->wheres = [];
        return $this;
    }

    protected function addWheres($field, $value)
    {
        $this->wheres[$field] = $value;
        return $this;
    }

    public function where($field, $value)
    {
        $this->addWheres($field, is_array($value) ? implode(',', $value) : $value);
        return $this;
    }
}
