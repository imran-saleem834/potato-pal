import moment from "moment";
import { getCategoriesByType, getSingleCategoryNameByType } from "@/helper.js";
import { binSizes } from "@/tonnes.js";

const samples = [
    { title: 'Dry Rot', data: 'dry_rot', allow: '2%' },
    { title: 'Black Scurf 2mm', data: 'black_scurf', allow: '2%' },
    { title: 'Powdery Scab', data: 'powdery_scab', allow: '2%' },
    { title: 'Root Knot Nematode', data: 'root_knot_nematode', allow: '2%' },
    { title: 'Soft Rots', data: 'soft_rots', allow: '0.25%' },
    { title: 'Pink Rot', data: 'pink_rot', allow: '0.25%' },
    { title: 'Sub Total', data: 'sub_total', allow: '2%' },
    { title: 'Common Scab', data: 'common_scab', allow: '4% (2%)' },
    { title: 'Total Disease', data: 'total_disease', allow: '4% (2%)' },
    { title: 'Black Scurf Scatter', data: 'black_scurf_scatter', allow: '10%' },
    { title: 'Insect Damage', data: 'insect_damage', allow: '1.5%' },
    { title: 'Malformed Tubers', data: 'malformed_tubers', allow: '2%' },
    { title: 'Mechanical Damage', data: 'mechanical_damage', allow: '2%' },
    { title: 'Stem End Discolour', data: 'stem_end_discolour', allow: '2%' },
    { title: 'Foreign Cultivars', data: 'foreign_cultivars', allow: '0%' },
    { title: 'Misc. Frost', data: 'misc_frost', allow: '1%' },
    { title: 'Total Defects', data: 'total_defects', allow: '2%' },
    { title: 'Minimal Insect Feeding', data: 'minimal_insect_feeding', allow: 'Additional 2%' },
    { title: 'Oversize', data: 'oversize' },
    { title: 'Undersize', data: 'undersize' },
].map((sample) => {
    sample.render = (data, type, row) => {
        if (!data) {
            return '';
        }
        if (data.slice(-1) === '') {
            return '';
        }
        if (sample.allow) {
            return `${data.slice(-1)}% - ${sample.allow}`
        }
        return `${data.slice(-1)}%`
    };
    return sample;
})

const columns = [
    {
        title: 'Tia Sample Id',
        data: 'id',
        render: function (data, type, row) {
            const url = route('tia-samples.index', { tiaSampleId: data });
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
        title: 'Grower',
        data: 'receival.grower',
        render: function (data, type, row) {
            if (data) {
                const url = route('users.index', { userId: data.id });
                return `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>`;
            }
            return '';
        }
    },
    {
        title: 'Grower Group',
        data: 'receival.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'grower-group').length) {
                return getSingleCategoryNameByType(categories, 'grower-group')
            }
            return '';
        }
    },
    {
        title: 'Seed Variety',
        data: 'receival.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'seed-variety').length) {
                return getSingleCategoryNameByType(categories, 'seed-variety')
            }
            return '';
        }
    },
    {
        title: 'Seed Generation',
        data: 'receival.categories',
        render: function (categories, type, row) {
            if (getCategoriesByType(categories, 'seed-generation').length) {
                return getSingleCategoryNameByType(categories, 'seed-generation')
            }
            return '';
        }
    },
    {
        title: 'Processor',
        data: 'processor',
        render: function (data, type, row) {
            return binSizes.find(system => system.value === data)?.label || '';
        }
    },
    {
        title: 'Size',
        data: 'size',
        render: function (data, type, row) {
            const sizes = [
                { value: '35-350g', label: '35 - 350g'},
                { value: '90mm', label: '90mm'},
                { value: '70mm', label: '70mm'},
            ]
            return sizes.find(system => system.value === data)?.label || '';
        }
    },
    {
        title: 'No. of tubers',
        data: 'tubers',
        render: function (data, type, row) {
            return data ? data.slice(-1) : '';
        }
    },
    {
        title: 'Disease Key',
        data: 'disease_scoring',
    },
    {
        title: 'Time Added',
        data: 'created_at',
        render: function (data, type, row) {
            return moment(data).format('DD/MM/YYYY hh:mm A')
        }
    },
]

export default [...columns, ...samples];