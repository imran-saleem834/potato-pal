import moment from 'moment';
import { getCategoriesByType, getSingleCategoryNameByType } from '@/helper.js';

export default [
  {
    title: 'Receival Id',
    data: 'id',
    render: function (data, type, row) {
      const url = route('receivals.index', { receivalId: data });
      return `<a href="${url}" class="text-black inertia-link">${data}</a>`;
    },
  },
  {
    title: 'Grower',
    data: 'grower',
    render: function (data, type, row) {
      const url = route('users.index', { userId: data.id });
      return row.grower ? `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>` : '-';
    },
  },
  {
    title: 'Paddock',
    data: 'paddocks',
    render: function (data, type, row) {
      return data?.length ? data[0] : '-';
    },
  },
  {
    title: 'Time Added',
    data: 'created_at',
    render: function (data, type, row) {
      return moment(data).format('DD/MM/YYYY hh:mm A');
    },
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
    title: 'Seed Variety',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-variety').length) {
        return getSingleCategoryNameByType(categories, 'seed-variety');
      }
      return '';
    },
  },
  {
    title: 'Seed Generation',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-generation').length) {
        return getSingleCategoryNameByType(categories, 'seed-generation');
      }
      return '';
    },
  },
  {
    title: 'Seed Class',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-class').length) {
        return getSingleCategoryNameByType(categories, 'seed-class');
      }
      return '';
    },
  },
  {
    title: 'Transport Co.',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'transport').length) {
        return getSingleCategoryNameByType(categories, 'transport');
      }
      return '';
    },
  },
  {
    title: 'Deliver Type',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'deliver-type').length) {
        return getSingleCategoryNameByType(categories, 'deliver-type');
      }
      return '';
    },
  },
  {
    title: 'Grower Docket No.',
    data: 'grower_docket_no',
  },
  {
    title: 'CHC Receival Docket No.',
    data: 'chc_receival_docket_no',
  },
  {
    title: 'Driver',
    data: 'driver_name',
  },
  {
    title: 'Status',
    data: 'status',
  },
  {
    title: 'Comments',
    data: 'comments',
  },
];
