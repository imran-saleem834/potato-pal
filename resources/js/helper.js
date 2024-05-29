import { binSizes } from '@/const.js';
import ConfirmToast from '@/Components/ConfirmToast.vue';
import { POSITION, useToast } from 'vue-toastification';

const toast = useToast();

export function getCategoriesByType(categories, type) {
  return (categories || []).filter((category) => category.type === type);
}

export function getDropDownOptions(items, isGrower = false, isBuyer = false) {
  return (items || []).map((item) => {
    return {
      value: item.id,
      label: isGrower ? item.grower_name : isBuyer ? item.buyer_name : item.name,
    };
  });
}

export function getCategoriesDropDownByType(categories, type) {
  return getDropDownOptions(getCategoriesByType(categories, type));
}

export function getCategoryIdsByType(categories, type) {
  return getCategoriesByType(categories, type).map((category) => category.category_id);
}

export function getSingleCategoryNameByType(categories, type) {
  return getCategoriesByType(categories, type)[0]?.category?.name;
}

export function getCategoryByKeyword(categories, type, keyword) {
  return getCategoriesByType(categories, type).find((category) => category.name.includes(keyword));
}

export function toCamelCase(string) {
  if (!string) return '';
  const words = string.toLowerCase().split(' ');
  const uppercaseWords = words.map((word) => {
    let splitWord = word.split('');
    splitWord[0] = splitWord[0].toUpperCase();
    return splitWord.join('');
  });
  return uppercaseWords.join(' ');
}

export function getBinSizesValue(binSize) {
  return getLabelFromItems(binSizes, binSize);
}

export function getLabelFromItems(items, value) {
  return items.find((item) => item.value === value).label;
}

export function toTonnes(weight) {
  const weighted = (weight / 1000).toFixed(3);
  return weighted + (weighted <= 1 ? ' tonne' : ' tonnes');
}

export function doConfirmToast(message, callback) {
  toast(
    {
      component: ConfirmToast,
      props: {
        message,
      },
      listeners: {
        callback,
      },
    },
    {
      position: POSITION.TOP_CENTER,
      timeout: false,
      hideProgressBar: true,
    },
  );
}
