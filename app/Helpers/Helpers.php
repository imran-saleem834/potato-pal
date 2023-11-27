<?php

use App\Models\Category;
use App\Models\CategoriesRelation;

function prePrint($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function createCategoriesRelation($forgingId, $categoryId, $model, $type)
{
    if (!is_int($categoryId)) {
        $categoryId = getCategoryId($categoryId, $type);
    }

    CategoriesRelation::firstOrCreate([
        'category_id'        => $categoryId,
        'categorizable_id'   => $forgingId,
        'categorizable_type' => $model,
        'type'               => $type,
    ]);

    return $categoryId;
}

function getCategoryId(string $name, string $type): int
{
    $category = Category::firstOrCreate(['name' => $name, 'type' => $type]);

    return $category->id;
}
