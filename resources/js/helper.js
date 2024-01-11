export function getCategoriesByType(categories, type) {
    return (categories || []).filter(category => category.type === type);
}

export function getDropDownOptions(items, isGrower = false) {
    return (items || []).map(item => {
        return {
            'value': item.id,
            'label': isGrower && item.grower_name ? `${item.name} (${item.grower_name})` : item.name
        };
    });
}

export function getCategoriesDropDownByType(categories, type) {
    return getDropDownOptions(getCategoriesByType(categories, type));
}

export function getCategoryIdsByType(categories, type) {
    return getCategoriesByType(categories, type).map(category => category.category_id);
}

export function getSingleCategoryNameByType(categories, type) {
    return getCategoriesByType(categories, type)[0]?.category?.name
}

export function toCamelCase(string) {
    if (!string) return '';
    const words = string.toLowerCase().split(' ');
    const uppercaseWords = words.map(word => {
        let splitWord = word.split('');
        splitWord[0] = splitWord[0].toUpperCase();
        return splitWord.join('');
    });
    return uppercaseWords.join(' ')
}
