<?php

namespace App\Helpers;

use App\Models\Category;
use Illuminate\Support\Arr;
use App\Models\CategoriesRelation;

class CategoriesHelper
{
    public static function createRelationOfTypes(array $inputs, int $forgingId, $model)
    {
        $categoryIds = [];
        $inputs      = static::updateInputsToTypes($inputs);
        foreach ($inputs as $type => $input) {
            if (empty($input)) {
                continue;
            }
            foreach (Arr::wrap($input) as $categoryId) {
                $categoryIds[] = static::createRelation($categoryId, $forgingId, $model, $type);
            }
        }

        static::deleteCategoryRelations($forgingId, $model, array_keys($inputs), $categoryIds);
    }

    public static function createRelation($categoryId, int $forgingId, $model, string $type): int
    {
        if (!is_numeric($categoryId)) {
            $categoryId = static::getCategoryId($categoryId, $type);
        }

        CategoriesRelation::firstOrCreate([
            'category_id'        => $categoryId,
            'categorizable_id'   => $forgingId,
            'categorizable_type' => $model,
            'type'               => $type,
        ]);

        return $categoryId;
    }

    public static function getCategoryId(string $name, string $type): int
    {
        $category = Category::firstOrCreate(['name' => $name, 'type' => $type]);

        return $category->id;
    }

    public static function deleteCategoryRelations(int $forgingId, $model, array $types = [], array $categoryIds = [])
    {
        return CategoriesRelation::where('categorizable_id', $forgingId)
            ->where('categorizable_type', $model)
            ->when($types, function ($query, $types) {
                return $query->whereIn('type', $types);
            })
            ->when($categoryIds, function ($query, $categoryIds) {
                return $query->whereNotIn('category_id', $categoryIds);
            })
            ->delete();
    }

    private static function updateInputsToTypes($inputs)
    {
        $fields = array_map(function ($field) {
            return str_replace('_', '-', $field);
        }, array_keys($inputs));

        return array_combine($fields, $inputs);
    }
}
