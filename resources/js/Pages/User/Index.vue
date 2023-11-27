<script setup>
import { Link, } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/User/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";

const users = ref(null);
const user = ref(null);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const categories = ref([]);

const getUsers = (keyword = null) => {
    axios.get(route('users.list'), { params: { keyword: keyword, userId: edit.value } }).then(response => {
        users.value = response.data.users;
        user.value = response.data.user || {};

        if (!edit.value) {
            setActiveTab(response.data.user?.id);
        } else {
            setEdit(edit.value);
        }
    });
};

const getUser = (id) => {
    axios.get(route('users.show', id)).then(response => {
        user.value = response.data;

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
    user.value = {};
    activeTab.value = null;
}

const deleteUser = (id) => {
    axios.delete(route('users.destroy', id), {
        onSuccess: () => {
            getUsers();
        },
    });
}

const getCategories = () => {
    axios.get(route('categories.index'), { params: { type: ['buyer', 'grower'] } }).then(response => {
        categories.value = response.data
        getUsers();
    });
}

getCategories();
</script>

<template>
    <AppLayout title="Users">
        <TopBar
            type="Users"
            @search="getUsers"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            v-if="user"
            type="Users"
            :title="user.name || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            @newRecord="setNewRecord"
            @editRecord="() => setEdit(user?.id)"
            @deleteRecord="() => deleteUser(user?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <LeftBar
                        :items="users"
                        :active-tab="activeTab"
                        :row-1="{title: 'Name', value: 'name'}"
                        :row-2="{title: 'Email', value: 'email'}"
                        @click="getUser"
                    />
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="user">
                        <div class="tab-pane active">
                            <Details
                                :user="user"
                                :is-edit="!!edit"
                                :is-new="isNewRecord"
                                :categories="categories"
                                @updateRecord="getCategories"
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
                                        <a role="button" @click="deleteUser(user?.id)">
                                            <span class="fa fa-trash-o"></span> Delete
                                        </a>
                                    </li>
                                    <li>
                                        <a role="button" @click="setEdit(user?.id)">
                                            <span class="fa fa-edit"></span>Edit
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-if="user">
                        <ol class="breadcrumb">
                            <li>
                                <Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link>
                            </li>
                            <li>
                                <Link href="" data-dismiss="modal" aria-label="Close">Users</Link>
                            </li>
                            <li class="active" v-if="isNewRecord">New</li>
                            <li class="active" v-else-if="user">{{ user.name }}</li>
                        </ol>
                        <Details
                            :user="user"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
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
