import moment from 'moment';
import { getCategoriesByType, getSingleCategoryNameByType, toTonnes } from '@/helper.js';

export default [
  {
    title: 'User ID',
    data: 'id',
    render: function (data, type, row) {
      const url = route('users.index', { userId: data });
      return `<a href="${url}" class="text-black inertia-link">${data}</a>`;
    },
  },
  {
    title: 'Name',
    data: 'name',
  },
  {
    title: 'Email',
    data: 'email',
  },
  {
    title: 'Username',
    data: 'username',
  },
  {
    title: 'Phone',
    data: 'phone',
  },
  {
    title: 'User Access',
    data: 'role',
    render: function (data, type, row) {
      return data.join(', ');
    },
  },
  {
    title: 'Cool Store',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'cool-store').length) {
        return getSingleCategoryNameByType(categories, 'cool-store');
      }
      return '';
    },
  },
  {
    title: 'Buyer Group',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'buyer-group').length) {
        return getSingleCategoryNameByType(categories, 'buyer-group');
      }
      return '';
    },
  },
  {
    title: 'Buyer Name',
    data: 'buyer_name',
  },
  {
    title: 'Grower Group',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'grower-group').length) {
        return getSingleCategoryNameByType(categories, 'grower-group');
      }
      return '';
    },
  },
  {
    title: 'Grower Name',
    data: 'grower_name',
  },
  {
    title: 'Time Added',
    data: 'created_at',
    render: function (data, type, row) {
      return moment(data).format('DD/MM/YYYY hh:mm A');
    },
  },
];
