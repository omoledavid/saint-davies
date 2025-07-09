<?php 

namespace App\Http\Filters;

class HotelFilter extends QueryFilter
{
    public function include($value)
    {
        $includes = is_array($value) ? $value : explode(',', $value);
        return $this->builder->with($includes);
    }
    public function search($value)
    {
        $this->builder->where(function($query) use ($value) {
            $query->where('name', 'like', '%' . $value . '%')
                  ->orWhere('description', 'like', '%' . $value . '%');
        });
    }
    public function status($value)
    {
        $this->builder->where('is_active', $value);
    }
}