import moment from 'moment';

export default [
  {
    title: 'Time Added',
    data: 'created_at',
    render: function (data, type, row) {
      return moment(data).format('DD/MM/YYYY hh:mm A');
    },
  },
  {
    title: 'Comments',
    data: 'comments',
  },
];
