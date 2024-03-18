import moment from 'moment';
import { toTonnes, getCategoriesByType, getSingleCategoryNameByType } from '@/helper.js';
import { binSizes } from '@/const.js';

export default [
  {
    title: 'Buyer Name',
    data: 'allocation.buyer',
    render: function (data, type, row) {
      const url = route('cuttings.index', { buyerId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
    },
  },
  {
    title: 'Grower',
    data: 'allocation.grower',
    render: function (data, type, row) {
      const url = route('users.index', { userId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>`;
    },
  },
  {
    title: 'Paddock',
    data: 'allocation.paddock',
  },
  {
    title: 'Seed Type',
    data: 'allocation.categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-type').length) {
        return getSingleCategoryNameByType(categories, 'seed-type');
      }
      return '';
    },
  },
  {
    title: 'Bin Size',
    data: 'allocation.bin_size',
    render: function (data, type, row) {
      return binSizes.find((binSize) => binSize.value === data)?.label;
    },
  },
  {
    title: 'Bins to cut',
    data: 'no_of_bins',
  },
  {
    title: 'Fungicide',
    data: 'cutting.categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'fungicide').length) {
        return getSingleCategoryNameByType(categories, 'fungicide');
      }
      return '';
    },
  },
  {
    title: 'Cut By',
    data: 'cutting.cut_by',
  },
  {
    title: 'Time of Cut',
    data: 'cutting.cut_date',
    render: function (data, type, row) {
      return data ? moment(data).format('DD/MM/YYYY') : '';
    },
  },
  {
    title: 'Grower Group',
    data: 'allocation.categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'grower-group').length) {
        return getSingleCategoryNameByType(categories, 'grower-group');
      }
      return '';
    },
  },
  {
    title: 'Seed Variety',
    data: 'allocation.categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-variety').length) {
        return getSingleCategoryNameByType(categories, 'seed-variety');
      }
      return '';
    },
  },
  {
    title: 'Seed Generation',
    data: 'allocation.categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-generation').length) {
        return getSingleCategoryNameByType(categories, 'seed-generation');
      }
      return '';
    },
  },
  {
    title: 'Seed Class',
    data: 'allocation.categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-class').length) {
        return getSingleCategoryNameByType(categories, 'seed-class');
      }
      return '';
    },
  },
  {
    title: 'Comments',
    data: 'cutting.comment',
  },
];
