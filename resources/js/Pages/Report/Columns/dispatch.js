import moment from 'moment';
import { getCategoriesByType, getSingleCategoryNameByType } from '@/helper.js';

function getAllocation(allocation, row) {
  if (row.dispatch_type === 'reallocation') {
    if (row.item.foreignable.item.foreignable.type === 'sizing') {
      allocation = row.item.foreignable.item.foreignable.item.foreignable.allocatable.sizeable;
    } else {
      allocation = row.item.foreignable.item.foreignable.item.foreignable;
    }
  } else if (row.dispatch_type === 'cutting') {
    if (row.item.foreignable.type === 'sizing') {
      allocation = row.item.foreignable.item.foreignable.allocatable.sizeable;
    } else {
      allocation = row.item.foreignable.item.foreignable;
    }
  } else if (row.dispatch_type === 'sizing') {
    allocation = row.item.foreignable.allocatable.sizeable;
  }

  return allocation;
}

export default [
  {
    title: 'Buyer Name',
    data: 'buyer',
    render: function (data, type, row) {
      const url = route('dispatches.index', { buyerId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
    },
  },
  {
    title: 'Grower',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let allocation = getAllocation(data, row);
      const grower = allocation.grower;

      const url = route('users.index', { userId: grower.id });
      return `<a href="${url}" class="text-black inertia-link">${grower.grower_name}</a>`;
    },
  },
  {
    title: 'Paddock',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let allocation = getAllocation(data, row);
      
      return allocation.paddock;
    },
  },
  {
    title: 'Seed Type',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let categories = data.categories;

      if (row.dispatch_type === 'reallocation') {
        categories = row.item.foreignable.item.foreignable.item.foreignable.categories;
      } else if (row.dispatch_type === 'cutting') {
        categories = row.item.foreignable.item.foreignable.categories;
      } else if (row.dispatch_type === 'sizing') {
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
      let allocation = getAllocation(data, row);
      
      if (getCategoriesByType(allocation.categories, 'grower-group').length) {
        return getSingleCategoryNameByType(allocation.categories, 'grower-group');
      }
      return '';
    },
  },
  {
    title: 'Seed Variety',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let allocation = getAllocation(data, row);
      
      if (getCategoriesByType(allocation.categories, 'seed-variety').length) {
        return getSingleCategoryNameByType(allocation.categories, 'seed-variety');
      }
      return '';
    },
  },
  {
    title: 'Seed Generation',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let allocation = getAllocation(data, row);
      
      if (getCategoriesByType(allocation.categories, 'seed-generation').length) {
        return getSingleCategoryNameByType(allocation.categories, 'seed-generation');
      }
      return '';
    },
  },
  {
    title: 'Seed Class',
    data: 'item.foreignable',
    render: function (data, type, row) {
      let allocation = getAllocation(data, row);
      
      if (getCategoriesByType(allocation.categories, 'seed-class').length) {
        return getSingleCategoryNameByType(allocation.categories, 'seed-class');
      }
      return '';
    },
  },
  {
    title: 'Group Type',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'buyer-group').length) {
        return getSingleCategoryNameByType(categories, 'buyer-group');
      }
      return '';
    },
  },
  {
    title: 'Transport',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'transport').length) {
        return getSingleCategoryNameByType(categories, 'transport');
      }
      return '';
    },
  },
  {
    title: 'Data Source',
    data: 'dispatch_type',
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
    title: 'Dispatch Time',
    data: 'created_at',
    render: function (data, type, row) {
      return moment(data).format('DD/MM/YYYY hh:mm A');
    },
  },
];
