<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends AbstractFilter
{
    public const NAME = 'name';

    protected function getCallbacks(): array
    {
        return [
            self::NAME => [$this, 'name'],
        ];
    }
    /**
     * Callback for name search param
     *
     * @param Builder $builder
     * @param string $value
     * @return void
     */
    public function name(Builder $builder, $value)
    {
        $splited = explode(' ', $value);
        
        foreach ($splited as $val){
            $builder->where('first_name', 'like', "%{$val}%");
            $builder->orWhere('last_name', 'like', "%{$val}%");
        }
    }
}
