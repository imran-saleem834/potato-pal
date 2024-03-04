import moment from "moment";
import { getCategoriesByType, getSingleCategoryNameByType, toTonnes } from "@/helper.js";
import { binSizes } from "@/const.js";

export default [
    {
        title: 'Buyer Name',
        data: 'buyer',
        render: function (data, type, row) {
            const url = route('reallocations.index', { buyerId: data.id });
            return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
        }
    },
    {
        title: 'Allocation Buyer Name',
        data: 'allocation_buyer',
        render: function (data, type, row) {
            const url = route('allocations.index', { buyerId: data.id });
            return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
        }
    },
    {
        title: 'Grower',
        data: 'allocation.grower',
        render: function (data, type, row) {
            const url = route('users.index', { userId: data.id });
            return `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>`;
        }
    },
    {
        title: 'Paddock',
        data: 'allocation.paddock'
    },
    {
        title: 'Seed Type',
        data: 'allocation.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'seed-type').length) {
                return getSingleCategoryNameByType(categories, 'seed-type')
            }
            return '';
        }
    },
    {
        title: 'Bin Size',
        data: 'allocation.bin_size',
        render: function (data, type, row) {
            return binSizes.find(binSize => binSize.value === data)?.label;
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
            return toTonnes(data);
        }
    },
    {
        title: 'Time Added',
        data: 'created_at',
        render: function (data, type, row) {
            return moment(data).format('DD/MM/YYYY hh:mm A')
        }
    },
    {
        title: 'Grower Group',
        data: 'allocation.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'grower-group').length) {
                return getSingleCategoryNameByType(categories, 'grower-group')
            }
            return '';
        }
    },
    {
        title: 'Seed Variety',
        data: 'allocation.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'seed-variety').length) {
                return getSingleCategoryNameByType(categories, 'seed-variety')
            }
            return '';
        }
    },
    {
        title: 'Seed Generation',
        data: 'allocation.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'seed-generation').length) {
                return getSingleCategoryNameByType(categories, 'seed-generation')
            }
            return '';
        }
    },
    {
        title: 'Seed Class',
        data: 'allocation.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'seed-class').length) {
                return getSingleCategoryNameByType(categories, 'seed-class')
            }
            return '';
        }
    },
    {
        title: 'Comments',
        data: 'comment',
    },
];