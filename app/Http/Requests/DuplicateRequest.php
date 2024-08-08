<?php

namespace App\Http\Requests;

use App\Models\Allocation;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DuplicateRequest extends FormRequest
{
    private $allocations = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'buyer_id'    => ['required', 'numeric', 'exists:users,id'],
            'half_tonnes' => ['required', 'numeric'],
            'one_tonnes'  => ['required', 'numeric'],
            'two_tonnes'  => ['required', 'numeric'],
            'weight'      => ['required', 'numeric'],
        ];

        foreach ($this->allocations as $allocation) {
            $halfCondition                                      = ($allocation->item->half_tonnes > 0) ? 'lt' : 'lte';
            $oneCondition                                       = ($allocation->item->one_tonnes > 0) ? 'lt' : 'lte';
            $twoCondition                                       = ($allocation->item->two_tonnes > 0) ? 'lt' : 'lte';
            $rules["allocations.{$allocation->id}.id"]          = ['required', 'numeric'];
            $rules["allocations.{$allocation->id}.half_tonnes"] = [
                'required',
                'numeric',
                'gte:0',
                "{$halfCondition}:{$allocation->item->half_tonnes}"
            ];
            $rules["allocations.{$allocation->id}.one_tonnes"]  = [
                'required',
                'numeric',
                'gte:0',
                "{$oneCondition}:{$allocation->item->one_tonnes}"
            ];
            $rules["allocations.{$allocation->id}.two_tonnes"]  = [
                'required',
                'numeric',
                'gte:0',
                "{$twoCondition}:{$allocation->item->two_tonnes}"
            ];
            $rules["allocations.{$allocation->id}.weight"]      = [
                'required',
                'numeric',
                'gte:0',
                "lte:{$allocation->item->weight}"
            ];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];
        foreach ($this->allocations as $allocation) {
            $attributes["allocations.{$allocation->id}.half_tonnes"] = 'half tonnes';
            $attributes["allocations.{$allocation->id}.one_tonnes"]  = 'one tonnes';
            $attributes["allocations.{$allocation->id}.two_tonnes"]  = 'two tonnes';
            $attributes["allocations.{$allocation->id}.weight"]      = 'weight';
        }
        return $attributes;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $allocationInputs  = $this->input('allocations');
        $allocationIds     = Arr::pluck($allocationInputs, ['id']);
        $this->allocations = Allocation::query()
            ->select(['id', 'grower_id', 'paddock'])
            ->with(['categories', 'item'])
            ->whereIn('id', $allocationIds)
            ->get();
        
        $inputAllocation = $this->input("allocations");
        foreach ($this->allocations as $allocation) {
            $weight                                     = max($inputAllocation[$allocation->id]['weight'], 0) * 1000;
            $inputAllocation[$allocation->id]['weight'] = $weight;
        }
        $this->merge(["allocations" => $inputAllocation]);
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        $growerId     = $paddock = $growerGroupId = $seedTypeId = $seedVarietyId = $seedGenerationId = $seedClassId = null;
        $isGrowerSame = $isPaddockSame = $isGrowerGroupSame = $isSeedTypeSame = $isSeedVarietySame = $isSeedGenerationSame = $isSeedClassSame = true;
        foreach ($this->allocations as $allocation) {
            if (empty($growerId)) {
                $growerId = $allocation->grower_id;
            } else if ($growerId !== $allocation->grower_id) {
                $isGrowerSame = false;
            }
            if (empty($paddock)) {
                $paddock = $allocation->paddock;
            } else if ($paddock !== $allocation->paddock) {
                $isPaddockSame = false;
            }
            foreach ($allocation->categories as $category) {
                if ($category->type === 'grower-group') {
                    if (empty($growerGroupId)) {
                        $growerGroupId = $category->category_id;
                    } else if ($growerGroupId !== $category->category_id) {
                        $isGrowerGroupSame = false;
                    }
                } else if ($category->type === 'seed-type') {
                    if (empty($seedTypeId)) {
                        $seedTypeId = $category->category_id;
                    } else if ($seedTypeId !== $category->category_id) {
                        $isSeedTypeSame = false;
                    }
                } else if ($category->type === 'seed-variety') {
                    if (empty($seedVarietyId)) {
                        $seedVarietyId = $category->category_id;
                    } else if ($seedVarietyId !== $category->category_id) {
                        $isSeedVarietySame = false;
                    }
                } else if ($category->type === 'seed-generation') {
                    if (empty($seedGenerationId)) {
                        $seedGenerationId = $category->category_id;
                    } else if ($seedGenerationId !== $category->category_id) {
                        $isSeedGenerationSame = false;
                    }
                } else if ($category->type === 'seed-class') {
                    if (empty($seedClassId)) {
                        $seedClassId = $category->category_id;
                    } else if ($seedClassId !== $category->category_id) {
                        $isSeedClassSame = false;
                    }
                }
            }
        }

        return [
            function (Validator $validator) use (
                $isGrowerSame,
                $isPaddockSame,
                $isGrowerGroupSame,
                $isSeedTypeSame,
                $isSeedVarietySame,
                $isSeedGenerationSame,
                $isSeedClassSame
            ) {
                if (!$isGrowerSame) {
                    $validator->errors()->add('allocations', 'Grower does not matched!');
                }
                if (!$isGrowerGroupSame) {
                    $validator->errors()->add('allocations', 'Grower group does not matched!');
                }
                if (!$isPaddockSame) {
                    $validator->errors()->add('allocations', 'Paddock does not matched!');
                }
                if (!$isSeedTypeSame) {
                    $validator->errors()->add('allocations', 'Seed type does not matched!');
                }
                if (!$isSeedVarietySame) {
                    $validator->errors()->add('allocations', 'Seed variety does not matched!');
                }
                if (!$isSeedGenerationSame) {
                    $validator->errors()->add('allocations', 'Seed generation does not matched!');
                }
                if (!$isSeedClassSame) {
                    $validator->errors()->add('allocations', 'Seed class does not matched!');
                }
            }
        ];
    }
}
