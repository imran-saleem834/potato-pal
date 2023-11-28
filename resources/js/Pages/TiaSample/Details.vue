<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { getCategoriesByType, toCamelCase } from "@/helper.js";
import moment from 'moment';
import Multiselect from '@vueform/multiselect';
import TextInput from "@/Components/TextInput.vue";
import MultipleBoxes from "@/Pages/TiaSample/MultipleBoxes.vue";

const props = defineProps({
    tiaSample: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
    receivals: Array,
    categories: Array,
});

const emit = defineEmits(['updateRecord']);

const addEmptyValues = (values, noOfValues) => {
    for (let i = 0; i <= (noOfValues - 1); i++) {
        values[i] = values[i] || '';
    }
    return values;
}

const displaySampleValue = (values) => {
    if (!values) return '';
    return values.slice(0, (values.length - 1))
        .filter(val => val !== '')
        .join(',  ') + ' - ' + values.slice(-1);
}

const sum = (accumulator, current) => parseFloat(accumulator) + parseFloat(current);

const changeSampleValue = (name) => {
    if (name === 'tubers') return '';
    const length = form[name].length - 1;
    const input = form[name].slice(0, length)
        .filter(val => val !== '')
        .reduce(sum, 0);

    form[name][length] = input * 100 / getTotalTurbersValue();
}

const getTotalTurbersValue = () => {
    return form.tubers.filter(val => val !== '').reduce(sum, 0);
}

const samples = [
    { title: 'No. of tubers Samples', name: 'tubers', allow: 'Allowable' },
    { title: 'Dry Rot', name: 'dry_rot', allow: '2%' },
    { title: 'Black Scurf 2mm', name: 'black_scurf', allow: '2%' },
    { title: 'Powdery Scab', name: 'powdery_scab', allow: '2%' },
    { title: 'Root Knot Nematode', name: 'root_knot_nematode', allow: '2%' },
    { title: 'Soft Rots', name: 'soft_rots', allow: '0.25%' },
    { title: 'Pink Rot', name: 'pink_rot', allow: '0.25%' },
    { title: 'Common Scab', name: 'common_scab', allow: '0.25%' },
    { title: 'Black Scurf Scatter', name: 'black_scurf_scatter', allow: '10%' },
    { title: 'Insect Damage', name: 'insect_damage', allow: '1.5%' },
    { title: 'Malformed Tubers', name: 'malformed_tubers', allow: '2%' },
    { title: 'Mechanical Damage', name: 'mechanical_damage', allow: '2%' },
    { title: 'Stem End Discolour', name: 'stem_end_discolour', allow: '2%' },
    { title: 'Foreign Cultivars', name: 'foreign_cultivars', allow: '0%' },
    { title: 'Misc. Frost', name: 'misc_frost', allow: '1%' },
    { title: 'Minimal Insect Feeding', name: 'minimal_insect_feeding', allow: 'Additional' },
    { title: 'Oversize', name: 'oversize', allow: '' },
    { title: 'Undersize', name: 'undersize', allow: '' },
]

samples.forEach(sample => {
    props.tiaSample[sample.name] = addEmptyValues(props.tiaSample[sample.name] || [], 5);
});

const form = useForm({
    receival_id: props.tiaSample.receival_id,
    processor: props.tiaSample.processor,
    inspection_no: props.tiaSample.inspection_no,
    inspection_date: props.tiaSample.inspection_date,
    cool_store: props.tiaSample.cool_store,
    size: props.tiaSample.size,
    tubers: props.tiaSample.tubers,
    dry_rot: props.tiaSample.dry_rot,
    black_scurf: props.tiaSample.black_scurf,
    powdery_scab: props.tiaSample.powdery_scab,
    root_knot_nematode: props.tiaSample.root_knot_nematode,
    soft_rots: props.tiaSample.soft_rots,
    pink_rot: props.tiaSample.pink_rot,
    common_scab: props.tiaSample.common_scab,
    black_scurf_scatter: props.tiaSample.black_scurf_scatter,
    insect_damage: props.tiaSample.insect_damage,
    malformed_tubers: props.tiaSample.malformed_tubers,
    mechanical_damage: props.tiaSample.mechanical_damage,
    stem_end_discolour: props.tiaSample.stem_end_discolour,
    foreign_cultivars: props.tiaSample.foreign_cultivars,
    misc_frost: props.tiaSample.misc_frost,
    minimal_insect_feeding: props.tiaSample.minimal_insect_feeding,
    oversize: props.tiaSample.oversize,
    undersize: props.tiaSample.undersize,
    status: props.tiaSample.status,
});

watch(() => props.tiaSample,
    (tiaSample) => {
        form.clearErrors();
        form.receival_id = tiaSample.receival_id
        form.processor = tiaSample.processor
        form.inspection_no = tiaSample.inspection_no
        form.inspection_date = tiaSample.inspection_date
        form.cool_store = tiaSample.cool_store
        form.size = tiaSample.size
        form.status = tiaSample.status
        samples.forEach(sample => {
            props.tiaSample[sample.name] = addEmptyValues(props.tiaSample[sample.name] || [], 5);
            form[sample.name] = addEmptyValues(tiaSample[sample.name] || [], 5);
        });
    }
);

watch(() => props.isNew,
    (isNew) => {
        if (isNew) {
            samples.forEach(sample => {
                props.tiaSample[sample.name] = addEmptyValues(props.tiaSample[sample.name] || [], 5);
            });
        }
    }
);

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const updateRecord = () => {
    form.patch(route('tia-sample.update', props.tiaSample.id), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const storeRecord = () => {
    form.post(route('tia-sample.store'), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}
</script>

<template>
    <div class="row">
        <div v-if="isEdit || isNew" class="col-md-12">
            <div class="flex-end create-update-btn">
                <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">
                    <span class="fa fa-edit"></span> Update
                </a>
                <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">
                    <span class="fa fa-edit"></span> Create
                </a>
            </div>
        </div>
        <div :class="colSize">
            <div class="user-boxes">
                <template v-if="isForm">
                    <h6>Receival</h6>
                    <Multiselect
                        v-model="form.receival_id"
                        mode="single"
                        placeholder="Choose a receival"
                        :searchable="true"
                        :options="receivals"
                    />
                    <div :class="{'has-error' : form.errors.receival_id}">
                        <span v-show="form.errors.receival_id" class="help-block text-left">{{
                                form.errors.receival_id
                            }}</span>
                    </div>
                </template>
                <template v-else>
                    <h6>Grower Name</h6>
                    <h5>{{ tiaSample?.receival?.user?.name }}</h5>

                    <h6>Tia Sample Id</h6>
                    <h5>{{ tiaSample.id }}</h5>
                </template>

                <template v-if="!isNew">
                    <h6>Receival Id</h6>
                    <h5>{{ tiaSample?.receival?.id }}</h5>

                    <h6>Time Added</h6>
                    <h5>{{ moment(tiaSample.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

                    <h6>Grower Group</h6>
                    <ul v-if="getCategoriesByType(tiaSample?.receival?.categories, 'grower')">
                        <li v-for="category in getCategoriesByType(tiaSample?.receival?.categories, 'grower')"
                            :key="category.id">
                            <a>{{ category.category?.name }}</a>
                        </li>
                    </ul>
                    <h5 v-else>-</h5>
                </template>

                <h6>Status</h6>
                <ul>
                    <li><a role="button" :class="{'btn-pending' : tiaSample.status === 'pending'}">{{
                            toCamelCase(tiaSample.status || 'pending')
                        }}</a></li>
                </ul>
            </div>

            <h4>Seed Details</h4>
            <div class="user-boxes">
                <template v-if="!isNew">
                    <h6>Seed Variety</h6>
                    <ul v-if="getCategoriesByType(tiaSample?.receival?.categories, 'seed-variety')">
                        <li v-for="category in getCategoriesByType(tiaSample?.receival?.categories, 'seed-variety')"
                            :key="category.id">
                            <a>{{ category.category?.name }}</a>
                        </li>
                    </ul>
                    <h5 v-else>-</h5>

                    <h6>Seed Generation</h6>
                    <ul v-if="getCategoriesByType(tiaSample?.receival?.categories, 'seed-generation')">
                        <li v-for="category in getCategoriesByType(tiaSample?.receival?.categories, 'seed-generation')"
                            :key="category.id">
                            <a>{{ category.category?.name }}</a>
                        </li>
                    </ul>
                    <h5 v-else>-</h5>

                    <h6>Grower Docket No</h6>
                    <h5 v-if="tiaSample?.receival?.grower_docket_no">{{ tiaSample.receival.grower_docket_no }}</h5>
                </template>

                <h6>Processor</h6>
                <ul v-if="isForm">
                    <li>
                        <a
                            role="button"
                            @click="() => form.processor = 'one-tone'"
                            :class="{'black-btn' : form.processor === 'one-tone'}"
                        >One Tone</a>
                    </li>
                    <li>
                        <a
                            role="button"
                            @click="() => form.processor = 'two-tone'"
                            :class="{'black-btn' : form.processor === 'two-tone'}"
                        >Two Tone</a>
                    </li>
                </ul>
                <h5 v-else-if="tiaSample.processor">{{
                        tiaSample.processor === 'one-tone' ? 'One Tone' : 'Two Tone'
                    }}</h5>

                <h6>Inspection No</h6>
                <TextInput v-if="isForm" v-model="form.inspection_no" :error="form.errors.inspection_no" type="text"/>
                <h5 v-else>{{ tiaSample.inspection_no }}</h5>

                <h6>Inspection Date</h6>
                <TextInput v-if="isForm" v-model="form.inspection_date" :error="form.errors.inspection_date"
                           type="date"/>
                <h5 v-else>{{ tiaSample.inspection_date }}</h5>

                <h6>Cool Store</h6>
                <TextInput v-if="isForm" v-model="form.cool_store" :error="form.errors.cool_store" type="text"/>
                <h5 v-else>{{ tiaSample.cool_store }}</h5>
            </div>

            <h4>Continue External Report</h4>
            <div class="user-boxes">
                <h6>Black Scurf Scatter</h6>
            </div>
        </div>
        <div :class="colSize">
            <h4>TIA Seed Potato Certificate Sheet</h4>
            <div class="user-boxes">
                <h6>Size</h6>
                <ul v-if="isForm">
                    <li>
                        <a
                            role="button"
                            @click="() => form.size = '35-350g'"
                            :class="{'black-btn' : form.size === '35-350g'}"
                        >35 - 350g</a>
                    </li>
                    <li>
                        <a
                            role="button"
                            @click="() => form.size = '90mm'"
                            :class="{'black-btn' : form.size === '90mm'}"
                        >90mm</a>
                    </li>
                    <li>
                        <a
                            role="button"
                            @click="() => form.size = '70mm'"
                            :class="{'black-btn' : form.size === '70mm'}"
                        >70mm</a>
                    </li>
                </ul>
                <h5 v-else-if="tiaSample.size">{{ tiaSample.size.replace('-', ' ') }}</h5>

                <template v-for="sample in samples" :key="sample.name">
<!--                    <MultipleBoxes-->
<!--                        :title="sample.title"-->
<!--                        :name="sample.name"-->
<!--                        :allow="sample.allow"-->
<!--                        :is-form="isForm"-->
<!--                        :values="tiaSample[sample.name]"-->
<!--                        :form-values="form[sample.name]"-->
<!--                        :errors="form.errors"-->
<!--                        @update="(index, value) => form[sample.name][index] = value"-->
<!--                        @change="changeSampleValue(sample.name)"-->
<!--                        @display="sample.name === 'tubers' ? getTotalTurbersValue() : displaySampleValue(tiaSample[sample.name])"-->
<!--                        v-model="form[sample.name]"-->
<!--                    />-->
                    <h6>{{ sample.title }}</h6>
                    <ul v-if="isForm" class="multiple-inputs">
                        <li v-for="(value, index) in tiaSample[sample.name]" :key="value">
                            <TextInput
                                v-model="form[sample.name][index]"
                                :disabled="index === 4"
                                type="text"
                                @keyup="changeSampleValue(sample.name)"
                            />
                        </li>
                        <li>{{ sample.allow }}</li>

                        <template
                            v-for="(value, index) in tiaSample[sample.name]"
                            :key="index"
                        >
                            <div v-show="form.errors[`${sample.name}.${index}`]" class="has-error">
                                <span class="help-block text-left">{{ form.errors[`${sample.name}.${index}`] }}</span>
                            </div>
                        </template>
                    </ul>
                    <h5 v-else>
                        {{
                            sample.name === 'tubers' ?
                                getTotalTurbersValue() :
                                displaySampleValue(tiaSample[sample.name])
                        }}
                    </h5>
                </template>
            </div>

            <h4>Continue External Report</h4>
            <div class="user-boxes">

            </div>

            <h4>Images</h4>
            <div class="user-boxes">

            </div>
        </div>
    </div>
</template>
