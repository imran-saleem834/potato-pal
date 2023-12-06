<script setup>
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import moment from 'moment';
import { getCategoriesByType } from "@/helper.js";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import UlLiButton from "@/Components/UlLiButton.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const props = defineProps({
    allocation: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
    growers: Object,
    buyers: Object,
});

const emit = defineEmits(['update', 'create']);

const selectReceival = ref({});

const form = useForm({
    buyer_id: props.allocation.buyer_id,
    grower_id: props.allocation.grower_id,
    unique_key: props.allocation.unique_key,
    allocated_type: props.allocation.allocated_type,
    allocated_bins: props.allocation.allocated_bins,
    allocated_tonnes: props.allocation.allocated_tonnes,
    tonnes_available_receivals: props.allocation.tonnes_available_receivals,
    bins_before_cutting: props.allocation.bins_before_cutting,
    tonnes_before_cutting: props.allocation.tonnes_before_cutting,
    cutting_date: props.allocation.cutting_date,
    bins_after_cutting: props.allocation.bins_after_cutting,
    tonnes_after_cutting: props.allocation.tonnes_after_cutting,
    reallocated_buyer_id: props.allocation.reallocated_buyer_id,
    tonnes_reallocated: props.allocation.tonnes_reallocated,
    bins_reallocated: props.allocation.bins_reallocated,
});

watch(() => props.allocation,
    (allocation) => {
        form.clearErrors();
        form.buyer_id = allocation.buyer_id
        form.grower_id = allocation.grower_id
        form.unique_key = allocation.unique_key
        form.allocated_type = allocation.allocated_type
        form.allocated_bins = allocation.allocated_bins
        form.allocated_tonnes = allocation.allocated_tonnes
        form.tonnes_available_receivals = allocation.tonnes_available_receivals
        form.bins_before_cutting = allocation.bins_before_cutting
        form.tonnes_before_cutting = allocation.tonnes_before_cutting
        form.cutting_date = allocation.cutting_date
        form.bins_after_cutting = allocation.bins_after_cutting
        form.tonnes_after_cutting = allocation.tonnes_after_cutting
        form.reallocated_buyer_id = allocation.reallocated_buyer_id
        form.tonnes_reallocated = allocation.tonnes_reallocated
        form.bins_reallocated = allocation.bins_reallocated

        if (allocation.unique_key) {
            selectReceivalOnEdit(allocation);
        }
    }
);

const selectReceivalOnEdit = (allocation) => {
    selectReceival.value = props.growers
        ?.find(grower => grower.value === allocation.grower_id)
        ?.receivals
        ?.find(receival => receival.unique_key === allocation.unique_key) || {};
}

selectReceivalOnEdit(props.allocation);

const onSelectReceival = (receival) => {
    selectReceival.value = receival
    form.unique_key = receival.unique_key
    form.allocated_type = null
    form.allocated_bins = null
    form.allocated_tonnes = null
}

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const updateRecord = () => {
    form.patch(route('allocations.update', props.allocation.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('update')
        },
    });
}

const storeRecord = () => {
    form.post(route('allocations.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('create')
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
        <div :class="colSize">
            <div class="user-boxes">
                <h6>Buyer Name</h6>
                <template v-if="isForm">
                    <Multiselect
                        v-model="form.buyer_id"
                        mode="single"
                        placeholder="Choose a buyer"
                        :searchable="true"
                        :options="buyers"
                    />
                    <div :class="{'has-error' : form.errors.buyer_id}">
                        <span v-show="form.errors.buyer_id" class="help-block text-left">
                            {{ form.errors.buyer_id }}
                        </span>
                    </div>
                </template>
                <template v-else>
                    <h5>{{ allocation.buyer?.name }}</h5>

                    <h6>Buyer Group</h6>
                    <ul v-if="getCategoriesByType(allocation.buyer?.categories, 'buyer')">
                        <li v-for="category in getCategoriesByType(allocation.buyer?.categories, 'buyer')" :key="category.id">
                            <a>{{ category.category.name }}</a>
                        </li>
                    </ul>

                    <h6>Buyer Id</h6>
                    <h5><Link :href="route('users.index', {userId: allocation.buyer_id})">{{ allocation.buyer_id }}</Link></h5>
                </template>
            </div>

            <h4>Allocations Details</h4>
            <div class="user-boxes">
                <h6>Grower Name</h6>
                <template v-if="isForm">
                    <Multiselect
                        v-model="form.grower_id"
                        mode="single"
                        placeholder="Choose a grower"
                        :searchable="true"
                        :options="growers"
                    />
                    <div :class="{'has-error' : form.errors.grower_id}">
                        <span v-show="form.errors.grower_id" class="help-block text-left">
                            {{ form.errors.grower_id }}
                        </span>
                    </div>
                </template>
                <template v-else>
                    <h5>{{ allocation.grower?.name }}</h5>

                    <h6>Grower Id</h6>
                    <h5><Link :href="route('users.index', {userId: allocation.grower_id})">{{ allocation.grower_id }}</Link></h5>
                </template>

                <button
                    v-if="isForm"
                    class="btn-red"
                    data-toggle="modal"
                    data-target="#receivals"
                    style="margin-top: 10px;"
                >Select Receival</button>

                <div v-if="Object.values(selectReceival).length && isForm" class="user-table" style="margin: 20px 0;">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Selected receival</th>
                            <th>Seed Bin Size</th>
                            <th>Oversize Bin Size</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ `${selectReceival.seed_type}, ${selectReceival.seed_variety}, ${selectReceival.seed_class}, ${selectReceival.seed_generation}, ${selectReceival.grower_group}, ${selectReceival.paddock}` }}</td>
                            <td>{{ selectReceival.seed_bin_size }} Tonnes</td>
                            <td>{{ selectReceival.oversize_bin_size }} Tonnes</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <h6>Allocated Type</h6>
                <UlLiButton
                    v-if="isForm"
                    :value="form.allocated_type"
                    :error="form.errors.allocated_type"
                    :items="[
                        {key: 'seed', value: 'Seed'},
                        {key: 'oversize', value: 'Oversize'},
                    ]"
                    @click="(key) => form.allocated_type = key"
                />
                <h5 v-else-if="form.allocated_type">{{ form.allocated_type === 'seed' ? 'Seed' : 'Oversize' }}</h5>
                <h5 v-else>-</h5>

                <h6>Allocated Bins</h6>
                <TextInput v-if="isForm" v-model="form.allocated_bins" :error="form.errors.allocated_bins" type="text"/>
                <h5 v-else>{{ allocation.allocated_bins }}</h5>

                <h6>Allocated Tonnes</h6>
                <TextInput v-if="isForm" v-model="form.allocated_tonnes" :error="form.errors.allocated_tonnes" type="text" />
                <h5 v-else>{{ allocation.allocated_tonnes }} Tonnes</h5>
            </div>
        </div>
        <div :class="colSize">
            <h4>Cutting Details</h4>
            <div class="user-boxes">
                <h6>Bins Before Cutting</h6>
                <TextInput v-if="isForm" v-model="form.bins_before_cutting" :error="form.errors.bins_before_cutting" type="text"/>
                <h5 v-else>{{ allocation.bins_before_cutting }}</h5>

                <h6>Tonnes Before Cutting</h6>
                <TextInput v-if="isForm" v-model="form.tonnes_before_cutting" :error="form.errors.tonnes_before_cutting" type="text"/>
                <h5 v-else>{{ allocation.tonnes_before_cutting }} Tonnes</h5>

                <h6>Cutting Date</h6>
                <TextInput v-if="isForm" v-model="form.cutting_date" :error="form.errors.cutting_date" type="datetime-local"/>
                <h5 v-else>{{ moment(allocation.cutting_date).format('DD/MM/YYYY hh:mm A') }}</h5>
            </div>

            <h4>Reallocated Details</h4>
            <div class="user-boxes">
                <h6>Bins After Cutting</h6>
                <TextInput v-if="isForm" v-model="form.bins_after_cutting" :error="form.errors.bins_after_cutting" type="text"/>
                <h5 v-else>{{ allocation.bins_after_cutting }}</h5>

                <h6>Tonnes After Cutting</h6>
                <TextInput v-if="isForm" v-model="form.tonnes_after_cutting" :error="form.errors.tonnes_after_cutting" type="text"/>
                <h5 v-else>{{ allocation.tonnes_after_cutting }} Tonnes</h5>

                <h6>Reallocated Buyer Name</h6>
                <template v-if="isForm">
                    <Multiselect
                        v-model="form.reallocated_buyer_id"
                        mode="single"
                        placeholder="Choose a reallocated buyer"
                        :searchable="true"
                        :options="buyers"
                    />
                    <div :class="{'has-error' : form.errors.reallocated_buyer_id}">
                        <span v-show="form.errors.reallocated_buyer_id" class="help-block text-left">
                            {{ form.errors.reallocated_buyer_id }}
                        </span>
                    </div>
                </template>
                <template v-else>
                    <h5>{{ allocation.reallocated_buyer?.name }}</h5>

                    <h6>Reallocated Buyer Id</h6>
                    <h5>
                        <Link :href="route('users.index', {userId: allocation.reallocated_buyer_id})">
                            {{ allocation.reallocated_buyer_id }}
                        </Link>
                    </h5>
                </template>

                <h6>Tonnes Reallocated</h6>
                <TextInput v-if="isForm" v-model="form.tonnes_reallocated" :error="form.errors.tonnes_reallocated" type="text"/>
                <h5 v-else>{{ allocation.tonnes_reallocated }}</h5>

                <h6>Bins Reallocated</h6>
                <TextInput v-if="isForm" v-model="form.bins_reallocated" :error="form.errors.bins_reallocated" type="text"/>
                <h5 v-else>{{ allocation.bins_reallocated }}</h5>
            </div>
        </div>
    </div>

    <div class="modal fade" id="receivals" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel3">Select Receival</h4>
                </div>
                <div class="modal-body">
                    <template v-for="grower in growers" :key="grower.value">
                        <div v-if="form.grower_id === grower.value && isForm" style="margin: 20px 0;">
                            <DataTable class="table">
                                <thead>
                                <tr>
                                    <th>Seed Type, Variety, Class, Generation</th>
                                    <th>Grower, Paddock</th>
                                    <th>Seed Bin Size</th>
                                    <th>Oversize Bin Size</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="receival in grower?.receivals" :key="receival.id">
                                    <tr
                                        v-if="receival.seed_bin_size > 0 || receival.oversize_bin_size > 0"
                                        @click="() => onSelectReceival(receival)"
                                        style="cursor: pointer"
                                        data-dismiss="modal"
                                        aria-label="Close"
                                    >
                                        <td>{{ `${receival.seed_type}, ${receival.seed_variety}, ${receival.seed_class}, ${receival.seed_generation}` }}</td>
                                        <td>{{ `${receival.grower_group}, ${receival.paddock}` }}</td>
                                        <td>{{ receival.seed_bin_size }} Tonnes</td>
                                        <td>{{ receival.oversize_bin_size }} Tonnes</td>
                                    </tr>
                                </template>
                                </tbody>
                            </DataTable>
                        </div>
                    </template>
                </div>
            </div>
            <!-- modal-content -->
        </div>
        <!-- modal-dialog -->
    </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
