<script setup>
import { computed, ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { getDropDownOptions, getCategoriesDropDownByType, getCategoryIdsByType } from "@/helper.js";
import moment from 'moment';
import Multiselect from '@vueform/multiselect';
import TextInput from "@/Components/TextInput.vue";

const paddockOptions = ref([]);

const props = defineProps({
    receival: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
    users: Array,
    categories: Array,
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
    user_id: props.receival.user_id,
    grower: getCategoryIdsByType(props.receival.categories, 'grower'),
    paddocks: props.receival.paddocks,
    seed_type: getCategoryIdsByType(props.receival.categories, 'seed-type'),
    oversize_bin_size: props.receival.oversize_bin_size,
    seed_bin_size: props.receival.seed_bin_size,
    seed_variety: getCategoryIdsByType(props.receival.categories, 'seed-variety'),
    seed_generation: getCategoryIdsByType(props.receival.categories, 'seed-generation'),
    seed_class: getCategoryIdsByType(props.receival.categories, 'seed-class'),
    delivery_type: getCategoryIdsByType(props.receival.categories, 'delivery-type'),
    fungicide: getCategoryIdsByType(props.receival.categories, 'fungicide'),
    transport: getCategoryIdsByType(props.receival.categories, 'transport'),
    grower_docket_no: props.receival.grower_docket_no,
    chc_receival_docket_no: props.receival.chc_receival_docket_no,
    driver_name: props.receival.driver_name,
    comments: props.receival.comments,
});

watch(() => props.receival,
    (receival) => {
        form.clearErrors();
        form.user_id = receival.user_id
        form.grower = getCategoryIdsByType(receival.categories, 'grower')
        form.paddocks = receival.paddocks
        form.seed_type = getCategoryIdsByType(receival.categories, 'seed-type')
        form.oversize_bin_size = receival.oversize_bin_size
        form.seed_bin_size = receival.seed_bin_size
        form.seed_variety = getCategoryIdsByType(receival.categories, 'seed-variety')
        form.seed_generation = getCategoryIdsByType(receival.categories, 'seed-generation')
        form.seed_class = getCategoryIdsByType(receival.categories, 'seed-class')
        form.delivery_type = getCategoryIdsByType(receival.categories, 'delivery-type')
        form.fungicide = getCategoryIdsByType(receival.categories, 'fungicide')
        form.transport = getCategoryIdsByType(receival.categories, 'transport')
        form.grower_docket_no = receival.grower_docket_no
        form.chc_receival_docket_no = receival.chc_receival_docket_no
        form.driver_name = receival.driver_name
        form.comments = receival.comments

        updatePaddock(receival.user_id);
    }
);

watch(() => form.user_id,
    (userId) => {
        updatePaddock(userId);
    }
);

const updatePaddock = (userId) => {
    if (userId) {
        const user = props.users.find(user => user.id === userId);
        paddockOptions.value = user.paddocks.map(paddock => {
            return `${paddock.name} (${paddock.hectares})`
        });
        return;
    }
    paddockOptions.value = [];
}

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const updateRecord = () => {
    form.patch(route('receivals.update', props.receival.id), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const storeRecord = () => {
    form.post(route('receivals.store'), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const pushForTiaSample = () => {
    form.post(route('receivals.push.tia-sample', props.receival.id), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const pushForUnload = () => {
    form.post(route('receivals.push.unload', props.receival.id), {
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
                <h6>Name</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.user_id"
                    mode="single"
                    placeholder="Choose a user"
                    :searchable="true"
                    :options="getDropDownOptions(users)"
                />
                <h5 v-else>{{ receival.user?.name }}</h5>

                <h6>Grower Group</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.grower"
                    mode="tags"
                    placeholder="Choose a grower group"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'grower')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'grower')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'grower')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'grower').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6 v-if="!isForm">Time Added</h6>
                <h5 v-if="!isForm">{{ moment(receival.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

                <h6>Paddock</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.paddocks"
                    mode="tags"
                    placeholder="Choose a paddock"
                    :searchable="true"
                    :create-option="true"
                    :options="paddockOptions"
                />
                <ul v-else-if="receival.paddocks">
                    <li v-for="paddock in receival.paddocks" :key="paddock">
                        <a>{{ paddock }}</a>
                    </li>
                </ul>
            </div>

            <h4>Seed Information</h4>
            <div class="user-boxes">
                <h6>Seed Variety</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.seed_variety"
                    mode="tags"
                    placeholder="Choose a seed variety"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'seed-variety')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'seed-variety')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'seed-variety')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'seed-variety').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Seed Generation</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.seed_generation"
                    mode="tags"
                    placeholder="Choose a seed generation"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'seed-generation')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'seed-generation')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'seed-generation')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'seed-generation').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Seed Class</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.seed_class"
                    mode="tags"
                    placeholder="Choose a seed class"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'seed-class')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'seed-class')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'seed-class')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'seed-class').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <div v-if="!isForm">
                    <h6>Tia Sample Status</h6>
                    <ul>
                        <li>
                            <a v-if="receival.tia_sample?.id" role="button">Pushed for TIA Sample</a>
                            <a v-else role="button" class="black-btn" @click="pushForTiaSample">Push for TIA Sample</a>
                        </li>
                    </ul>

                    <h6>TIA Sample ID</h6>
                    <h5 v-if="receival.tia_sample?.id">{{ receival.tia_sample?.id }}</h5>
                    <h5 v-else>-</h5>
                </div>
            </div>
        </div>
        <div :class="colSize">
            <h4>Unloading Information</h4>
            <div class="user-boxes">
                <div v-if="!isForm">
                    <h6>Unloading Status</h6>
                    <ul>
                        <li>
                            <a v-if="receival.unload?.id" role="button">Pushed for Unload</a>
                            <a v-else role="button" class="black-btn" @click="pushForUnload">Push for Unload</a>
                        </li>
                    </ul>

                    <h6>Unloading ID</h6>
                    <h5 v-if="receival.unload?.id">{{ receival.unload.id }}</h5>
                    <h5 v-else>-</h5>
                </div>

                <h6>Seed Type</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.seed_type"
                    mode="tags"
                    placeholder="Choose a seed type"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'seed-type')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'seed-type')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'seed-type')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'seed-type').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Oversize Bin Size</h6>
                <ul v-if="isForm">
                    <li>
                        <a
                            role="button"
                            @click="() => form.oversize_bin_size = 'one-tone'"
                            :class="{'black-btn' : form.oversize_bin_size === 'one-tone'}"
                        >One Tone</a>
                    </li>
                    <li>
                        <a
                            role="button"
                            @click="() => form.oversize_bin_size = 'two-tone'"
                            :class="{'black-btn' : form.oversize_bin_size === 'two-tone'}"
                        >Two Tone</a>
                    </li>
                </ul>
                <h5 v-else-if="receival.oversize_bin_size">{{ receival.oversize_bin_size === 'one-tone' ? 'One Tone' : 'Two Tone' }}</h5>

                <h6>Seed Bin Size</h6>
                <ul v-if="isForm">
                    <li>
                        <a
                            role="button"
                            @click="() => form.seed_bin_size = 'one-tone'"
                            :class="{'black-btn' : form.seed_bin_size === 'one-tone'}"
                        >One Tone</a>
                    </li>
                    <li>
                        <a
                            role="button"
                            @click="() => form.seed_bin_size = 'two-tone'"
                            :class="{'black-btn' : form.seed_bin_size === 'two-tone'}"
                        >Two Tone</a>
                    </li>
                </ul>
                <h5 v-else-if="receival.seed_bin_size">{{ receival.seed_bin_size === 'one-tone' ? 'One Tone' : 'Two Tone' }}</h5>
            </div>

            <h4>Other Information</h4>
            <div class="user-boxes">
                <h6>Transport Co.</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.transport"
                    mode="tags"
                    placeholder="Choose a transport"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'transport')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'transport')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'transport')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'transport').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Delivery Type</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.delivery_type"
                    mode="tags"
                    placeholder="Choose a delivery type"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'delivery-type')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'delivery-type')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'delivery-type')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'delivery-type').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Growers’s Docket No</h6>
                <TextInput v-if="isForm" v-model="form.grower_docket_no" :error="form.errors.grower_docket_no" type="text"/>
                <h5 v-else>{{ receival.grower_docket_no }}</h5>

                <h6>CHC Receival Docket No</h6>
                <TextInput v-if="isForm" v-model="form.chc_receival_docket_no"
                           :error="form.errors.chc_receival_docket_no" type="text"/>
                <h5 v-else>{{ receival.chc_receival_docket_no }}</h5>

                <h6>Fungicide</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.fungicide"
                    mode="tags"
                    placeholder="Choose a fungicide"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'fungicide')"
                />
                <ul v-else-if="getCategoryIdsByType(receival.categories, 'fungicide')">
                    <li v-for="group in getCategoryIdsByType(receival.categories, 'fungicide')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'fungicide').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Driver’s Name</h6>
                <TextInput v-if="isForm" v-model="form.driver_name" :error="form.errors.driver_name" type="text"/>
                <h5 v-else>{{ receival.driver_name }}</h5>

                <h6>Comments</h6>
                <TextInput v-if="isForm" v-model="form.comments" :error="form.errors.comments" type="text"/>
                <h5 v-else>{{ receival.comments }}</h5>
            </div>
        </div>
    </div>
</template>
