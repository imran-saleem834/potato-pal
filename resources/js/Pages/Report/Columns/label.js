import moment from 'moment';

export default [
  {
    title: 'Label Type',
    data: 'labelable_type',
    render: function (data, type, row) {
      const labelTypes = [
        { value: 'App\\Models\\Allocation', label: 'Allocation' },
        { value: 'App\\Models\\Reallocation', label: 'Reallocation' },
        { value: 'App\\Models\\CuttingAllocation', label: 'Cutting' },
      ];

      const labeled = labelTypes.find((labelType) => labelType.value === data).label;

      let url = '';
      if (labeled === 'Cutting') {
        url = route('cuttings.index', { buyerId: row.buyer_id });
      } else if (labeled === 'Reallocation') {
        url = route('reallocations.index', { buyerId: row.buyer_id });
      } else {
        url = route('allocations.index', { buyerId: row.buyer_id });
      }

      return `<a href="${url}" class="text-black inertia-link">${labeled} ID: ${row.labelable_id}</a>`;
    },
  },
  {
    title: 'Label Id',
    data: 'id',
    render: function (data, type, row) {
      const url = route('labels.index', { labelId: data });
      return `<a href="${url}" class="text-black inertia-link">ID: ${data}</a>`;
    },
  },
  {
    title: 'Issue to',
    data: 'buyer',
    render: function (data, type, row) {
      const url = route('users.index', { userId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
    },
  },
  {
    title: 'Ex Grower',
    data: 'grower',
    render: function (data, type, row) {
      const url = route('users.index', { userId: data.id });
      return `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>`;
    },
  },
  {
    title: 'Paddock',
    data: 'paddock',
  },
  {
    title: 'Time Generated',
    data: 'created_at',
    render: function (data, type, row) {
      return moment(data).format('DD/MM/YYYY hh:mm A');
    },
  },
  {
    title: 'Override Comments',
    data: 'comments',
  },
];
