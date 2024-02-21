import moment from "moment";
import { getCategoriesByType, getSingleCategoryNameByType } from "@/helper.js";
import { binSizes } from "@/tonnes.js";

export default [
    {
        title: 'Unload Id',
        data: 'receival_id',
        render: function (data, type, row) {
            const url = route('unloading.index', { receivalId: data });
            return `<a href="${url}" class="text-black inertia-link">${data}</a>`;
        }
    },
    {
        title: 'Receival Id',
        data: 'receival_id',
        render: function (data, type, row) {
            const url = route('receivals.index', { receivalId: data });
            return `<a href="${url}" class="text-black inertia-link">${data}</a>`;
        }
    },
    {
        title: 'Weighbridge Id',
        data: 'id',
    },
    {
        title: 'Seed Type',
        data: 'categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'seed-type').length) {
                return getSingleCategoryNameByType(categories, 'seed-type')
            }
            return '';
        }
    },
    {
        title: 'Channel',
        data: 'channel',
        render: function (data, type, row) {
            const channels = [
                { value: 'weighbridge', label: 'BU1' },
                { value: 'BU2', label: 'BU2' },
                { value: 'BU3', label: 'BU3' },
            ];
            return channels.find(channel => channel.value === data)?.label;
        }
    },
    {
        title: 'Bin Size',
        data: 'bin_size',
        render: function (data, type, row) {
            return binSizes.find(binSize => binSize.value === data)?.label;
        }
    },
    {
        title: 'System',
        data: 'system',
        render: function (data, type, row) {
            const systems = [
                { value: 1, label: 'System 1' },
                { value: 2, label: 'System 2' }
            ];
            return systems.find(system => system.value === data)?.label;
        }
    },
    {
        title: 'No of Bins',
        data: 'no_of_bins',
    },
    {
        title: 'Weight',
        data: 'weight',
        render: function (data, type, row) {
            return data + ' kg';
        }
    },
    {
        title: 'Grower',
        data: 'receival.grower',
        render: function (data, type, row) {
            const url = route('users.index', { userId: data.id });
            return `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>`;
        }
    },
    {
        title: 'Paddock',
        data: 'receival.paddocks',
        render: function (data, type, row) {
            return data?.length ? data[0] : '';
        }
    },
    {
        title: 'Fungicide',
        data: 'receival.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'fungicide').length) {
                return getSingleCategoryNameByType(categories, 'fungicide')
            }
            return '';
        }
    },
    {
        title: 'Time Added',
        data: 'created_at',
        render: function (data, type, row) {
            return moment(data).format('DD/MM/YYYY hh:mm A')
        }
    },
]
