import moment from 'moment';
import { getCategoriesByType, getSingleCategoryNameByType, toTonnes } from '@/helper.js';
import { binSizes } from '@/const.js';

export default [
  {
    title: 'Buyer Name',
    data: 'buyer',
    render: function (data, type, row) {
      const url = route('reallocations.index', { buyerId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
    },
  },
  {
    title: 'Ex Buyer Name',
    data: 'allocation_buyer',
    render: function (data, type, row) {
      const url = route('allocations.index', { buyerId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
    },
  },
  {
    title: 'Grower',
    data: 'item.foreignable.item.foreignable',
    render: function (data, type, row) {
      data = data.grower;
      if (row.item.foreignable.type === 'sizing') {
        data = row.item.foreignable.item.foreignable.allocatable.sizeable.grower;
      }
      const url = route('users.index', { userId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>`;
    },
  },
  {
    title: 'Paddock',
    data: 'item.foreignable.item.foreignable',
    render: function (data, type, row) {
      if (row.item.foreignable.type === 'sizing') {
        data = row.item.foreignable.item.foreignable.allocatable.sizeable;
      }
      return data.paddock;
    },
  },
  {
    title: 'Seed Type',
    data: 'item.foreignable.item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.item.foreignable.type === 'sizing') {
        categories = row.item.foreignable.item.foreignable.categories;
      }
      if (getCategoriesByType(categories, 'seed-type').length) {
        return getSingleCategoryNameByType(categories, 'seed-type');
      }
      return '';
    },
  },
  {
    title: 'Grower Group',
    data: 'item.foreignable.item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.item.foreignable.type === 'sizing') {
        categories = row.item.foreignable.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'grower-group').length) {
        return getSingleCategoryNameByType(categories, 'grower-group');
      }
      return '';
    },
  },
  {
    title: 'Seed Variety',
    data: 'item.foreignable.item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.item.foreignable.type === 'sizing') {
        categories = row.item.foreignable.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'seed-variety').length) {
        return getSingleCategoryNameByType(categories, 'seed-variety');
      }
      return '';
    },
  },
  {
    title: 'Seed Generation',
    data: 'item.foreignable.item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.item.foreignable.type === 'sizing') {
        categories = row.item.foreignable.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'seed-generation').length) {
        return getSingleCategoryNameByType(categories, 'seed-generation');
      }
      return '';
    },
  },
  {
    title: 'Seed Class',
    data: 'item.foreignable.item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;
      if (row.item.foreignable.type === 'sizing') {
        categories = row.item.foreignable.item.foreignable.allocatable.sizeable.categories;
      }
      if (getCategoriesByType(categories, 'seed-class').length) {
        return getSingleCategoryNameByType(categories, 'seed-class');
      }
      return '';
    },
  },
  {
    title: 'Data Source',
    data: 'item.foreignable.type',
  },
  {
    title: 'Tipped Bins',
    data: 'item.foreignable.item',
    render: function (item, type, row) {
      if (row.item.foreignable.type === 'sizing') {
        return '-';
      }
      return binSizes.find((binSize) => binSize.value === item.foreignable.item.bin_size)?.label + ' X ' + item.foreignable.item.no_of_bins;
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
    title: 'Comments',
    data: 'comment',
  },
  {
    title: 'Time Added',
    data: 'created_at',
    render: function (data, type, row) {
      return moment(data).format('DD/MM/YYYY hh:mm A');
    },
  },
];
