<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { getCategoriesByType, toCamelCase } from "@/helper.js";
import moment from 'moment';
import Multiselect from '@vueform/multiselect';
import TextInput from "@/Components/TextInput.vue";
import UlLiButton from "@/Components/UlLiButton.vue";

const props = defineProps({
    unload: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
    receivals: Array,
    categories: Array,
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
    receival_id: props.unload?.receival_id,
    total_seed_bins: props.unload.total_seed_bins,
    weight_seed_bins: props.unload.weight_seed_bins,
    total_oversize_bins: props.unload.total_oversize_bins,
    weight_oversize_bins: props.unload.weight_oversize_bins,
    status: props.unload.status,
});

watch(() => props.unload,
    (unload) => {
        form.clearErrors();
        form.receival_id = unload?.receival_id
        form.total_seed_bins = unload.total_seed_bins
        form.weight_seed_bins = unload.weight_seed_bins
        form.total_oversize_bins = unload.total_oversize_bins
        form.weight_oversize_bins = unload.weight_oversize_bins
        form.status = unload.status
    }
);

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const updateRecord = () => {
    form.patch(route('unloading.update', props.unload.id), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const storeRecord = () => {
    form.post(route('unloading.store'), {
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
                <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
                <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">Create</a>
            </div>
        </div>
        <div v-if="!isNew" :class="colSize">
            <div class="user-boxes">
                <h6>Name</h6>
                <h5>{{ unload?.receival?.user?.name }}</h5>

                <h6>Unloading Id</h6>
                <h5>{{ unload.id }}</h5>

                <h6>Receival Id</h6>
                <h5>{{ unload?.receival?.id }}</h5>

                <h6>Time Added</h6>
                <h5>{{ moment(unload.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

                <h6>Paddock</h6>
                <ul v-if="unload?.receival?.paddocks">
                    <li v-for="paddock in unload?.receival.paddocks" :key="paddock">
                        <a>{{ paddock }}</a>
                    </li>
                </ul>
                <h5 v-else>-</h5>

                <h6>Status</h6>
                <ul>
                    <li>
                        <a role="button"
                           :class="{'btn-pending' : unload.status === 'pending'}"
                        >{{ toCamelCase(unload.status) }}</a>
                    </li>
                </ul>
            </div>

            <h4>Important Information</h4>
            <div class="user-boxes">
                <h6>TIA Sampled</h6>
                <ul>
                    <li><a role="button" class="btn-pending">Pending</a></li>
                </ul>

                <h6>Fungicide</h6>
                <ul v-if="getCategoriesByType(unload?.receival?.categories, 'fungicide')">
                    <li v-for="category in getCategoriesByType(unload?.receival?.categories, 'fungicide')"
                        :key="category.id">
                        <a>{{ category.category?.name }}</a>
                    </li>
                </ul>
                <h5 v-else>-</h5>

                <h6>Oversize Bin Size</h6>
                <h5 v-if="unload?.receival?.oversize_bin_size">
                    {{ unload.receival.oversize_bin_size === 1 ? 'One Tone' : 'Two Tone' }}
                </h5>
                <h5 v-else>-</h5>

                <h6>Seed Bin Size</h6>
                <h5 v-if="unload?.receival?.seed_bin_size">
                    {{ unload.receival.seed_bin_size === 1 ? 'One Tone' : 'Two Tone' }}
                </h5>
                <h5 v-else>-</h5>
            </div>
        </div>
        <div :class="colSize">
            <h4>Seed information to Record</h4>
            <div class="user-boxes">
                <div v-if="isForm">
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
                </div>

                <h6>Number of seed bins</h6>
                <TextInput v-if="isForm" v-model="form.total_seed_bins" :error="form.errors.total_seed_bins"
                           type="text"/>
                <h5 v-else>{{ unload.total_seed_bins }}</h5>

                <h6>Weight of seed bins</h6>
                <TextInput v-if="isForm" v-model="form.weight_seed_bins" :error="form.errors.weight_seed_bins"
                           type="text"/>
                <h5 v-else>{{ unload.weight_seed_bins }}</h5>

                <h6>Number of oversize bins</h6>
                <TextInput v-if="isForm" v-model="form.total_oversize_bins" :error="form.errors.total_oversize_bins"
                           type="text"/>
                <h5 v-else>{{ unload.total_oversize_bins }}</h5>

                <h6>Weight of oversize bins</h6>
                <TextInput v-if="isForm" v-model="form.weight_oversize_bins" :error="form.errors.weight_oversize_bins"
                           type="text"/>
                <h5 v-else>{{ unload.weight_oversize_bins }}</h5>

                <div v-if="isForm">
                    <h6>Status</h6>
                    <UlLiButton
                        :value="form.status"
                        :error="form.errors.status"
                        :items="[
                            {key: 'pending', value: 'Pending'},
                            {key: 'completed', value: 'Completed'},
                        ]"
                        @click="(key) => form.status = key"
                    />
                </div>
            </div>
        </div>
    </div>
</template>