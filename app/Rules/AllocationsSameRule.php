<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllocationsSameRule implements ValidationRule
{
    protected $allocations;

    public function __construct($allocations)
    {
        $this->allocations = $allocations;
    }
    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->allocations as $key => $allocation) {
            if (empty($growerId)) {
                $growerId = $allocation->grower_id;
            } else {
                $growerId === $allocation->grower_id;
            }
        }
    }
}
