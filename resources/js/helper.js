export function getCategoriesByType(categories, type) {
    return (categories || []).filter((category) => category.type === type);
}

export function getDropDownOptions(items) {
    return (items || []).map(item => {
        return { 'value': item.id, 'label': item.name };
    });
}

export function getCategoriesDropDownByType(categories, type) {
    return getDropDownOptions(getCategoriesByType(categories, type));
}

export function getCategoryIdsByType(categories, type) {
    return getCategoriesByType(categories, type).map((category) => category.category_id);
}
