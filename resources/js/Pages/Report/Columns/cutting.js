import moment from 'moment';
import { toTonnes, getCategoriesByType, getSingleCategoryNameByType } from '@/helper.js';
import { binSizes } from '@/const.js';

export default [
  {
    title: 'Buyer Name',
    data: 'buyer',
    render: function (data, type, row) {
      const url = route('cuttings.index', { buyerId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
    },
  },
  {
    title: 'Grower',
    data: 'item.foreignable',
    render: function (data, type, row) {
      data = data.grower
      if (row.type === 'sizing') {
        data = row.item.foreignable.allocatable.sizeable.grower;
      }
      const url = route('users.index', { userId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>`;
    },
  },
  {
    title: 'Paddock',
    data: 'item.foreignable',
    render: function (data, type, row) {
      if (row.type === 'sizing') {
        data = row.item.foreignable.allocatable.sizeable;
      }
      return data.paddock;
    },
  },
  {
    title: 'Seed Type',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.type === 'sizing') {
        categories = row.item.foreignable.categories;
      }
      if (getCategoriesByType(categories, 'seed-type').length) {
        return getSingleCategoryNameByType(categories, 'seed-type');
      }
      return '';
    },
  },
  {
    title: 'Grower Group',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.type === 'sizing') {
        categories = row.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'grower-group').length) {
        return getSingleCategoryNameByType(categories, 'grower-group');
      }
      return '';
    },
  },
  {
    title: 'Seed Variety',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.type === 'sizing') {
        categories = row.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'seed-variety').length) {
        return getSingleCategoryNameByType(categories, 'seed-variety');
      }
      return '';
    },
  },
  {
    title: 'Seed Generation',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.type === 'sizing') {
        categories = row.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'seed-generation').length) {
        return getSingleCategoryNameByType(categories, 'seed-generation');
      }
      return '';
    },
  },
  {
    title: 'Seed Class',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.type === 'sizing') {
        categories = row.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'seed-class').length) {
        return getSingleCategoryNameByType(categories, 'seed-class');
      }
      return '';
    },
  },
  {
    title: 'Data Source',
    data: 'type',
  },
  {
    title: 'Tipped Bins',
    data: 'item',
    render: function (item, type, row) {
      if (row.type === 'sizing') {
        return '-';
      }
      return binSizes.find((binSize) => binSize.value === item.bin_size)?.label + ' X ' + item.no_of_bins;
    },
  },
  {
    title: 'Half Tonnes',
    data: 'item.half_tonnes',
  },
  {
    title: 'One Tonnes',
    data: 'item.one_tonnes',
  },
  {
    title: 'Two Tonnes',
    data: 'item.two_tonnes',
  },
  {
    title: 'Fungicide',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'fungicide').length) {
        return getSingleCategoryNameByType(categories, 'fungicide');
      }
      return '';
    },
  },
  {
    title: 'Cut By',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'cool-store').length) {
        return getSingleCategoryNameByType(categories, 'cool-store');
      }
      return '';
    },
  },
  {
    title: 'Time of Cut',
    data: 'cut_date',
    render: function (data, type, row) {
      return data ? moment(data).format('DD/MM/YYYY') : '';
    },
  },
  {
    title: 'Comments',
    data: 'comment',
  },
];
