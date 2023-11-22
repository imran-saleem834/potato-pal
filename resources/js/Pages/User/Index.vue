<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import UserDetails from '@/Components/UserDetails.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    name: '',
    email: '',
    username: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const users = ref(null);
const user = ref(null);
const activeTab = ref(null);
const isUserEdit = ref(null);
const isUserNew = ref(false);

const getUsers = (keyword = null) => {
    axios.get(route('users.list'), { params: {keyword: keyword} }).then(response => {
        users.value = response.data.users;
        user.value = response.data.user || {};

        setActiveTab(response.data.user?.id);
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
    isUserEdit.value = null;
    isUserNew.value = false;
};

const setEditUser = (id) => {
    isUserEdit.value = isUserEdit.value === id ? null : id;
    isUserNew.value = false;
}

const setNewUser = () => {
    isUserNew.value = true;
    isUserEdit.value = null;
    user.value = {}
    activeTab.value = null;
}

const deleteUser = (id) => {
    form.delete(route('users.destroy', id), {
        onSuccess: () => {
           console.log('Hello user destroy');
        },
    });
}

getUsers();
</script>

<template>
    <AppLayout title="Users">
        <TopBar
            type="Users"
            @search="getUsers"
            @newRecord="setNewUser"
        />
        <MiddleBar
            v-if="user"
            type="Users"
            :title="user.name"
            @newRecord="setNewUser"
            @editRecord="() => setEditUser(user?.id)"
            @deleteRecord="() => deleteUser(user?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <ul class="nav nav-tabs tabs-left sideways">
                        <li
                            v-for="user in users"
                            :key="user.id"
                            :class="{'active' : activeTab === user.id}"
                        >
                            <a
                                role="button"
                                :data-toggle="$windowWidth <= 767 ? 'modal' : 'tab'"
                                :data-target="$windowWidth <= 767 ? '#user-details' : ''"
                                @click="getUser(user.id)"
                            >
                                <div class="user-table">
                                    <table class="table">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                        </tr>
                                        <tr>
                                            <td v-text="user.name" />
                                            <td v-text="user.email" />
                                        </tr>
                                    </table>
                                    <span class="fa fa-angle-right angle-right"></span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="user">
                        <div class="tab-pane active">
                            <UserDetails
                                :user="user"
                                :is-edit="!!isUserEdit"
                                :is-new="isUserNew"
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
        <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fa fa-arrow-left"></span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel3">{{ $page.props.auth.user.name }}</h4>
                        <div class="modal-menu">
                            <div v-if="!isUserNew" class="btn-group">
                                <button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="fa fa-ellipsis-v"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a role="button" @click="deleteUser(user?.id)"><span class="fa fa-trash-o"></span> Delete</a></li>
                                    <li><a role="button" @click="setEditUser(user?.id)"><span class="fa fa-edit"></span> Edit</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-if="user">
                        <ol class="breadcrumb">
                            <li><Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link></li>
                            <li><Link href="" data-dismiss="modal" aria-label="Close">Users</Link></li>
                            <li class="active" v-if="isUserNew">New</li>
                            <li class="active" v-else-if="user">{{ user.name }}</li>
                        </ol>
                        <UserDetails
                            :user="user"
                            :is-edit="!!isUserEdit"
                            :is-new="isUserNew"
                            col-size="col-md-12"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
