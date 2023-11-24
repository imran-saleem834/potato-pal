<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Receival/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";

const receivals = ref([]);
const receival = ref(null);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const growerGroups = ref([]);
const buyerGroups = ref([]);

const getReceivals = (keyword = null) => {
    axios.get(route('receivals.list'), { params: { keyword: keyword, receivalId: edit.value } }).then(response => {
        receivals.value = response.data.receivals;
        receival.value = response.data.receival || {};

        if (!edit.value) {
            setActiveTab(response.data.receival?.id);
        } else {
            setEditUser(edit.value);
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

const setEditUser = (id) => {
    edit.value = edit.value === id ? null : id;
    isNewRecord.value = false;
}

const setNewRecord = () => {
    isNewRecord.value = true;
    edit.value = null;
    receival.value = {};
    activeTab.value = null;
}

const deleteUser = (id) => {
    axios.delete(route('receivals.destroy', id), {
        onSuccess: () => {
            getReceivals();
        },
    });
}

const getCategories = (hardRefresh = false) => {
    const categories = JSON.parse(localStorage.getItem('categories'));
    if (!hardRefresh && categories) {
        growerGroups.value = categories.filter(category => category.type === 'grower').map(category => {
            return { 'value': category.id, 'label': category.name };
        });
        buyerGroups.value = categories.filter(category => category.type === 'buyer').map(category => {
            return { 'value': category.id, 'label': category.name };
        });

        getReceivals();
        return;
    }

    axios.get(route('categories.index'), { params: { type: ['buyer', 'grower'] } }).then(response => {
        localStorage.setItem('categories', JSON.stringify(response.data));

        getCategories();
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
            :title="receival.user?.name || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            @newRecord="setNewRecord"
            @editRecord="() => setEditUser(receival?.id)"
            @deleteRecord="() => deleteUser(receival?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <LeftBar
                        :items="receivals"
                        :active-tab="activeTab"
                        :row-1="{title: 'Grower\'s Name', value: 'user.name'}"
                        :row-2="{title: 'Receival Id', value: 'id'}"
                        @click="getReceival"
                    />
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="receival">
                        <div class="tab-pane active">
                            <Details
                                :receival="receival"
                                :is-edit="!!edit"
                                :is-new="isNewRecord"
                                :grower-groups="growerGroups"
                                :buyer-groups="buyerGroups"
                                @updateRecord="() => getCategories(true)"
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
                                        <a role="button" @click="deleteUser(receival?.id)">
                                            <span class="fa fa-trash-o"></span> Delete
                                        </a>
                                    </li>
                                    <li>
                                        <a role="button" @click="setEditUser(receival?.id)">
                                            <span class="fa fa-edit"></span>Edit
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-if="receival">
                        <ol class="breadcrumb">
                            <li>
                                <Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link>
                            </li>
                            <li>
                                <Link href="" data-dismiss="modal" aria-label="Close">Receivals</Link>
                            </li>
                            <li class="active" v-if="isNewRecord">New</li>
                            <li class="active" v-else-if="receival">{{ receival.user?.name }}</li>
                        </ol>
                        <Details
                            :receival="receival"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            :grower-groups="growerGroups"
                            :buyer-groups="buyerGroups"
                            @updateRecord="() => getCategories(true)"
                            col-size="col-md-12"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
