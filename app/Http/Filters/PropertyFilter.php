<?php 

namespace App\Http\Filters;

class PropertyFilter extends QueryFilter
{
    public function include($value)
    {
        // Ensure $value is an array
        $includes = is_array($value) ? $value : explode(',', $value);

        return $this->builder->with($includes);
    }
    public function search($value)
    {
        $this->builder->where('title', 'like', '%' . $value . '%');
    }

    public function status($value)
    {
        $this->builder->where('status', $value);
    }

    public function property_type($value)
    {
        $this->builder->where('property_type', $value);
    }

    public function property_category($value)
    {
        $this->builder->where('property_category', $value);
    }

    public function is_available($value)
    {
        $this->builder->where('is_available', $value);
    }

    public function price($value)
    {
        $this->builder->where('price', $value);
    }

    public function rent_price($value)
    {
        $this->builder->where('rent_price', $value);
    }
    public function id($value)
    {
        $this->builder->where('id', $value);
    }
}