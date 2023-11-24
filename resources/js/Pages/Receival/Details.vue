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
                <TextInput v-if="isForm" v-model="form.user_id" :error="form.errors.user_id" type="text"/>
                <h5 v-else>{{ receival.user?.name }}</h5>
            </div>

            <h4>Seed Information</h4>
            <div class="user-boxes">

                <ul>
                    <li><a role="button" class="black-btn">Push for TIA Sample</a></li>
                </ul>

                <h6>TIA Sample ID</h6>
                <TextInput v-if="isForm" v-model="form.tia_sample_id" :error="form.errors.tia_sample_id" type="text"/>
                <h5 v-else>{{ receival.tia_sample_id }}</h5>
            </div>
        </div>
        <div :class="colSize">
            <h4>Unloading Information</h4>
            <div class="user-boxes">
                <h6>Unloading Status</h6>
                <ul>
                    <li><a role="button" class="black-btn">Push for Unload</a></li>
                </ul>
            </div>

            <h4>Other Information</h4>
            <div class="user-boxes">
                <h6>Transport Co</h6>
                <TextInput v-if="isForm" v-model="form.transport" :error="form.errors.transport" type="text"/>
                <h5 v-else>{{ receival.transport }}</h5>

                <h6>Growers’s Docket No</h6>
                <TextInput v-if="isForm" v-model="form.grower_docket_no" :error="form.errors.grower_docket_no" type="text"/>
                <h5 v-else>{{ receival.grower_docket_no }}</h5>

                <h6>CHC Receival Docket No</h6>
                <TextInput v-if="isForm" v-model="form.chc_receival_docket_no"
                           :error="form.errors.chc_receival_docket_no" type="text"/>
                <h5 v-else>{{ receival.chc_receival_docket_no }}</h5>

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

<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    receival: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
    growerGroups: Array,
    buyerGroups: Array,
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
    user_id: props.receival.user_id,
    tia_sample_id: props.receival.tia_sample_id,
    transport: props.receival.transport,
    grower_docket_no: props.receival.grower_docket_no,
    chc_receival_docket_no: props.receival.chc_receival_docket_no,
    driver_name: props.receival.driver_name,
    comments: props.receival.comments,
});

watch(() => props.receival,
    (receival) => {
        form.clearErrors();
        form.user_id = receival.user_id
        form.tia_sample_id = receival.tia_sample_id
        form.transport = receival.transport
        form.grower_docket_no = receival.grower_docket_no
        form.chc_receival_docket_no = receival.chc_receival_docket_no
        form.driver_name = receival.driver_name
        form.comments = receival.comments
    }
);

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
</script>
