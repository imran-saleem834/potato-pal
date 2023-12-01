<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Allocation/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";

const props = defineProps({
    allocations: Object,
    single: Object,
    filters: Object,
});

const allocation = ref(props.single);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const users = ref([]);

watch(search, (value) => {
    router.get(
        route('allocations.index'),
        { search: value },
        { preserveState: true, preserveScroll: true },
    )
});

const filter = (keyword) => search.value = keyword;

const getAllocation = (id) => {
    axios.get(route('allocations.show', id)).then(response => {
        allocation.value = response.data;

        setActiveTab(response.data.id);
    });
};

const setActiveTab = (id) => {
    activeTab.value = id;
    edit.value = null;
    isNewRecord.value = false;
};

const setEdit = (id) => {
    edit.value = edit.value === id ? null : id;
    isNewRecord.value = false;
}

const setNewRecord = () => {
    isNewRecord.value = true;
    edit.value = null;
    allocation.value = {};
    activeTab.value = null;
}

const deleteAllocation = (id) => {
    const form = useForm({});
    form.delete(route('allocations.destroy', id), {
        preserveState: true,
        onSuccess: () => {
            onCreatedRecord();
        },
    });
}

const onCreatedRecord = () => {
    setActiveTab(props.single.id)
    allocation.value = props.single;
}

setActiveTab(allocation?.value?.id);

const getUsers = () => {
    axios.get(route('allocations.users')).then(response => {
        users.value = response.data;
    });
}

getUsers();
</script>

<template>
    <AppLayout title="Allocations">
        <TopBar
            type="Allocations"
            :value="search"
            @search="filter"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            type="Allocations"
            :title="allocation?.name || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            @newRecord="setNewRecord"
            @editRecord="() => setEdit(allocation?.id)"
            @deleteRecord="() => deleteAllocation(allocation?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <LeftBar
                        :items="allocations"
                        :active-tab="activeTab"
                        :row-1="{title: 'Buyer Name', value: 'buyer.name'}"
                        :row-2="{title: 'Allocation Id', value: 'id'}"
                        @click="getAllocation"
                    />
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="allocation">
                        <div class="tab-pane active">
                            <Details
                                :allocation="allocation"
                                :is-edit="!!edit"
                                :is-new="isNewRecord"
                                :users="users"
                                @update="() => getAllocation(edit)"
                                @create="onCreatedRecord"
                                col-size="col-md-6"
                            />
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- tab-section -->

        <!-- Modal -->
        <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <ModalHeader
                        title="Allocations"
                        :is-new="isNewRecord"
                        @edit="() => setEdit(allocation?.id)"
                        @delete="() => deleteAllocation(allocation?.id)"
                    />
                    <div class="modal-body" v-if="allocation">
                        <ModalBreadcrumb
                            page="Allocations"
                            :title="allocation?.name || 'Allocations'"
                        />
                        <Details
                            :allocation="allocation"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            :users="users"
                            @update="() => getAllocation(edit)"
                            @create="onCreatedRecord"
                            col-size="col-md-12"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
