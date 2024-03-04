import moment from "moment";
import { getCategoriesByType, getSingleCategoryNameByType, toTonnes } from "@/helper.js";
import { binSizes } from "@/const.js";

export default [
    {
        title: 'Buyer Name',
        data: 'buyer',
        render: function (data, type, row) {
            const url = route('dispatches.index', { buyerId: data.id });
            return `<a href="${url}" class="text-black inertia-link">${data.buyer_name}</a>`;
        }
    },
    {
        title: 'Re/Allocation Buyer Name',
        data: 'id',
        render: function (data, type, row) {
            const buyer = row.reallocation_id ? row.reallocation.buyer : row.allocation.buyer;
            const url = route(row.reallocation_id ? 'reallocations.index' : 'allocations.index', { buyerId: buyer.id });
            return `<a href="${url}" class="text-black inertia-link">${buyer.buyer_name}</a>`;
        }
    },
    {
        title: 'Grower',
        data: 'id',
        render: function (data, type, row) {
            const grower = row.reallocation_id ? row.reallocation.allocation.grower : row.allocation.grower;
            const url = route('users.index', { userId: grower.id });
            return `<a href="${url}" class="text-black inertia-link">${grower.grower_name}</a>`;
        }
    },
    {
        title: 'Paddock',
        data: 'id',
        render: function (data, type, row) {
            const allocation = row.reallocation_id ? row.reallocation.allocation : row.allocation;
            return allocation.paddock;
        }
    },
    {
        title: 'Seed Type',
        data: 'id',
        render: function (data, type, row) {
            const allocation = row.reallocation_id ? row.reallocation.allocation : row.allocation;
            if (getCategoriesByType(allocation.categories, 'seed-type').length) {
                return getSingleCategoryNameByType(allocation.categories, 'seed-type')
            }
            return '';
        }
    },
    {
        title: 'Bin Size',
        data: 'id',
        render: function (data, type, row) {
            const allocation = row.reallocation_id ? row.reallocation.allocation : row.allocation;
            return binSizes.find(binSize => binSize.value === allocation.bin_size)?.label;
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
        data: 'id',
        render: function (data, type, row) {
            const allocation = row.reallocation_id ? row.reallocation.allocation : row.allocation;
            if (getCategoriesByType(allocation.categories, 'grower-group').length) {
                return getSingleCategoryNameByType(allocation.categories, 'grower-group')
            }
            return '';
        }
    },
    {
        title: 'Seed Variety',
        data: 'id',
        render: function (data, type, row) {
            const allocation = row.reallocation_id ? row.reallocation.allocation : row.allocation;
            if (getCategoriesByType(allocation.categories, 'seed-variety').length) {
                return getSingleCategoryNameByType(allocation.categories, 'seed-variety')
            }
            return '';
        }
    },
    {
        title: 'Seed Generation',
        data: 'id',
        render: function (data, type, row) {
            const allocation = row.reallocation_id ? row.reallocation.allocation : row.allocation;
            if (getCategoriesByType(allocation.categories, 'seed-generation').length) {
                return getSingleCategoryNameByType(allocation.categories, 'seed-generation')
            }
            return '';
        }
    },
    {
        title: 'Seed Class',
        data: 'id',
        render: function (data, type, row) {
            const allocation = row.reallocation_id ? row.reallocation.allocation : row.allocation;
            if (getCategoriesByType(allocation.categories, 'seed-class').length) {
                return getSingleCategoryNameByType(allocation.categories, 'seed-class')
            }
            return '';
        }
    },
    {
        title: 'Comments',
        data: 'comment',
    },
];