<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Receival/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";

const props = defineProps({
    users: Array,
});

const receivals = ref([]);
const receival = ref({});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const categories = ref([]);

const getReceivals = (keyword = null) => {
    axios.get(route('receivals.list'), { params: { keyword: keyword, receivalId: edit.value } }).then(response => {
        receivals.value = response.data.receivals;
        receival.value = response.data.receival || {};

        if (!edit.value) {
            setActiveTab(response.data.receival?.id);
        } else {
            setEdit(edit.value);
        }
    });
};

const getReceival = (id) => {
    axios.get(route('receivals.show', id)).then(response => {
        receival.value = response.data;

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
    receival.value = {};
    activeTab.value = null;
}

const deleteReceival = (id) => {
    axios.delete(route('receivals.destroy', id), {
        onSuccess: () => {
            getReceivals();
        },
    });
}

const getCategories = () => {
    const type = ['grower', 'seed-type', 'seed-variety', 'seed-generation', 'seed-class', 'delivery-type', 'fungicide', 'transport'];
    axios.get(route('categories.list'), { params: { type } }).then(response => {
        categories.value = response.data;
        getReceivals();
    });
}

getCategories();
</script>

<template>
    <AppLayout title="Receivals">
        <TopBar
            type="Receivals"
            @search="getReceivals"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            v-if="receival"
            type="Receivals"
            :title="receival.grower?.name || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            :access="{
                new: true,
                edit: Object.values(receival).length > 0,
                delete: Object.values(receival).length > 0,
            }"
            @newRecord="setNewRecord"
            @editRecord="() => setEdit(receival?.id)"
            @deleteRecord="() => deleteReceival(receival?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <LeftBar
                        :items="receivals"
                        :active-tab="activeTab"
                        :row-1="{title: 'Grower\'s Name', value: 'grower.name'}"
                        :row-2="{title: 'Receival Id', value: 'id'}"
                        @click="getReceival"
                    />
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="Object.values(receival).length > 0 || isNewRecord">
                        <div class="tab-pane active">
                            <Details
                                :receival="receival"
                                :is-edit="!!edit"
                                :is-new="isNewRecord"
                                :users="users"
                                :categories="categories"
                                @updateRecord="getCategories"
                                col-size="col-md-6"
                            />
                        </div>
                    </div>
                    <div class="col-sm-12" v-if="Object.values(receival).length <= 0 && !isNewRecord">
                        <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- tab-section -->

        <!-- Modal -->
        <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel3">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <ModalHeader
                        title="Receivals"
                        :is-new="isNewRecord"
                        @edit="() => setEdit(receival?.id)"
                        @delete="() => deleteReceival(receival?.id)"
                    />
                    <div class="modal-body" v-if="receival">
                        <ModalBreadcrumb
                            page="Receivals"
                            :title="receival?.grower?.name || 'New'"
                        />
                        <Details
                            :receival="receival"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            :users="users"
                            :categories="categories"
                            @updateRecord="getCategories"
                            col-size="col-md-12"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
