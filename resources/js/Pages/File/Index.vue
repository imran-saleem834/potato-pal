<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/File/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";

const files = ref([]);
const file = ref(null);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);

const getFiles = (keyword = null) => {
    axios.get(route('files.list'), { params: { keyword: keyword, fileId: edit.value } }).then(response => {
        files.value = response.data.files;
        file.value = response.data.file || {};

        if (!edit.value) {
            setActiveTab(response.data.file?.id);
        } else {
            setEdit(edit.value);
        }
    });
};

const getFile = (id) => {
    axios.get(route('files.show', id)).then(response => {
        file.value = response.data;

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
    file.value = {};
    activeTab.value = null;
}

const deleteFile = (id) => {
    axios.delete(route('files.destroy', id), {
        onSuccess: () => {
            getFiles();
        },
    });
}

getFiles();
</script>

<template>
    <AppLayout title="Files">
        <TopBar
            type="Files"
            @search="getFiles"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            v-if="file"
            type="Files"
            :title="file.title || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            @newRecord="setNewRecord"
            @editRecord="() => setEdit(file?.id)"
            @deleteRecord="() => deleteFile(file?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <LeftBar
                        :items="files"
                        :active-tab="activeTab"
                        :row-1="{title: 'Title', value: 'title'}"
                        :row-2="{title: 'File Id', value: 'id'}"
                        @click="getFile"
                    />
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="file">
                        <div class="tab-pane active">
                            <Details
                                :file="file"
                                :is-edit="!!edit"
                                :is-new="isNewRecord"
                                @updateRecord="getFiles"
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
                                        <a role="button" @click="deleteFile(file?.id)">
                                            <span class="fa fa-trash-o"></span> Delete
                                        </a>
                                    </li>
                                    <li>
                                        <a role="button" @click="setEdit(file?.id)">
                                            <span class="fa fa-edit"></span>Edit
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-if="file">
                        <ol class="breadcrumb">
                            <li>
                                <Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link>
                            </li>
                            <li>
                                <Link href="" data-dismiss="modal" aria-label="Close">files</Link>
                            </li>
                            <li class="active" v-if="isNewRecord">New</li>
                            <li class="active" v-else-if="file">{{ file.title }}</li>
                        </ol>
                        <Details
                            :file="file"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            @updateRecord="getFiles"
                            col-size="col-md-12"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
