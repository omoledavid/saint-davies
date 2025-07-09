<?php 

namespace App\Http\Filters;

class CarFilter extends QueryFilter
{
    public function include($value)
    {
        $includes = is_array($value) ? $value : explode(',', $value);
        return $this->builder->with($includes);
    }
    public function search($value)
    {
        $this->builder->where('title', 'like', '%' . $value . '%');
    }
    public function status($value)
    {
        $this->builder->where('is_available', $value);
    }
    public function price($value)
    {
        $this->builder->where('price', $value);
    }
    public function id($value)
    {
        $this->builder->where('id', $value);
    }
    public function condition($value)
    {
        $this->builder->where('condition', $value);
    }
    public function transmission($value)
    {
        $this->builder->where('transmission', $value);
    }
    public function fuel_type($value)
    {
        $this->builder->where('fuel_type', $value);
    }
    public function type($value)
    {
        $this->builder->where('type', $value);
    }
    protected function min($value)
    {
        $this->builder->where('price', '>=', $value);
    }

    protected function max($value)
    {
        $this->builder->where('price', '<=', $value);
    }
    
}