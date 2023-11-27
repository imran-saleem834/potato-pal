<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Unload/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";

const props = defineProps({
    users: Array,
});

const unloads = ref([]);
const unload = ref(null);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const categories = ref([]);

const getUnloads = (keyword = null) => {
    axios.get(route('unloading.list'), { params: { keyword: keyword, unloadId: edit.value } }).then(response => {
        unloads.value = response.data.unloads;
        unload.value = response.data.unload || {};

        if (!edit.value) {
            setActiveTab(response.data.unload?.id);
        } else {
            setEdit(edit.value);
        }
    });
};

const getUnload = (id) => {
    axios.get(route('unloading.show', id)).then(response => {
        unload.value = response.data;

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
    unload.value = {};
    activeTab.value = null;
}

const deleteUnload = (id) => {
    axios.delete(route('unloading.destroy', id), {
        onSuccess: () => {
            getUnloads();
        },
    });
}

getUnloads();
</script>

<template>
    <AppLayout title="Unloading">
        <TopBar
            type="Unloading"
            @search="getUnloads"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            v-if="unload"
            type="Unloading"
            :title="unload?.receival?.user?.name || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            @newRecord="setNewRecord"
            @editRecord="() => setEdit(unload?.id)"
            @deleteRecord="() => deleteUnload(unload?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <LeftBar
                        :items="unloads"
                        :active-tab="activeTab"
                        :row-1="{title: 'Grower\'s Name', value: 'receival.user.name'}"
                        :row-2="{title: 'Unloading Id', value: 'id'}"
                        @click="getUnload"
                    />
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="unload">
                        <div class="tab-pane active">
                            <Details
                                :unload="unload"
                                :is-edit="!!edit"
                                :is-new="isNewRecord"
                                :users="users"
                                @updateRecord="getUnloads"
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
        <div class="modal right fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fa fa-arrow-left"></span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel2">Users</h4>
                    </div>
                    <div class="modal-body">
                        <ul>
                            <li><a href="">Unique ID <span class="fa fa-angle-right"></span> </a></li>
                            <li><a href="">Name <span class="fa fa-angle-right"></span> </a></li>
                            <li><a href="">Email <span class="fa fa-angle-right"></span> </a></li>
                            <li><a href="">Username <span class="fa fa-angle-right"></span> </a></li>
                            <li><a href="">User Access <span class="fa fa-angle-right"></span> </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- modal -->

        <!-- Modal -->
        <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel3">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fa fa-arrow-left"></span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel3">{{ $page.props.auth.user.name }}</h4>
                        <div class="modal-menu">
                            <div v-if="!isNewRecord" class="btn-group">
                                <button type="button" class="dropdown-toggle" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                    <span class="fa fa-ellipsis-v"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a role="button" @click="deleteUnload(unload?.id)">
                                            <span class="fa fa-trash-o"></span> Delete
                                        </a>
                                    </li>
                                    <li>
                                        <a role="button" @click="setEdit(unload?.id)">
                                            <span class="fa fa-edit"></span>Edit
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-if="unload">
                        <ol class="breadcrumb">
                            <li>
                                <Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link>
                            </li>
                            <li>
                                <Link href="" data-dismiss="modal" aria-label="Close">Unloading</Link>
                            </li>
                            <li class="active" v-if="isNewRecord">New</li>
                            <li class="active" v-else-if="unload">{{ unload?.receival?.user?.name }}</li>
                        </ol>
                        <Details
                            :unload="unload"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            :users="users"
                            @updateRecord="getUnloads"
                            col-size="col-md-12"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
