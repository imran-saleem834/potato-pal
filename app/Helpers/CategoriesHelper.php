<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\CategoriesRelation;

class CategoriesHelper
{
    public static function createRelationOfTypes(array $inputs, int $forgingId, $model)
    {
        $categoryIds = [];
        foreach ($inputs as $key => $input) {
            $type = str_replace('_', '-', $key);
            foreach ($input as $categoryId) {
                $categoryIds[] = static::createRelation($categoryId, $forgingId, $model, $type);
            }
        }

        static::deleteCategoryRealtions($forgingId, $model, $categoryIds);
    }

    public static function createRelation($categoryId, int $forgingId, $model, string $type): int
    {
        if (!is_int($categoryId)) {
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

    public static function deleteCategoryRealtions(int $forgingId, $model, array $categoryIds = [])
    {
        CategoriesRelation::where('categorizable_id', $forgingId)
            ->where('categorizable_type', $model)
            ->when($categoryIds, function ($query, $categoryIds) {
                return $query->whereNotIn('category_id', $categoryIds);
            })
            ->delete();
    }
}
