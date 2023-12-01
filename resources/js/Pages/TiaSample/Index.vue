<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/TiaSample/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";

const props = defineProps({
    receivals: Array,
});

const tiaSamples = ref([]);
const tiaSample = ref(null);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const categories = ref([]);

const getTiaSamples = (keyword = null) => {
    axios.get(route('tia-samples.list'), { params: { keyword: keyword, tiaSampleId: edit.value } }).then(response => {
        tiaSamples.value = response.data.tiaSamples;
        tiaSample.value = response.data.tiaSample || {};

        if (!edit.value) {
            setActiveTab(response.data.tiaSample?.id);
        } else {
            setEdit(edit.value);
        }
    });
};

const getTiaSample = (id) => {
    axios.get(route('tia-samples.show', id)).then(response => {
        tiaSample.value = response.data;

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
    tiaSample.value = {};
    activeTab.value = null;
}

const deleteTiaSample = (id) => {
    axios.delete(route('tia-samples.destroy', id), {
        onSuccess: () => {
            getTiaSamples();
        },
    });
}

getTiaSamples();
</script>

<template>
    <AppLayout title="Tia Sample">
        <TopBar
            type="Tia Sample"
            @search="getTiaSamples"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            v-if="tiaSample"
            type="Tia Sample"
            :title="tiaSample?.receival?.user?.name || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            @newRecord="setNewRecord"
            @editRecord="() => setEdit(tiaSample?.id)"
            @deleteRecord="() => deleteTiaSample(tiaSample?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <LeftBar
                        :items="tiaSamples"
                        :active-tab="activeTab"
                        :row-1="{title: 'Grower\'s Name', value: 'receival.user.name'}"
                        :row-2="{title: 'Tia Sample Id', value: 'id'}"
                        @click="getTiaSample"
                    />
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content" v-if="tiaSample">
                        <div class="tab-pane active">
                            <Details
                                :tia-sample="tiaSample"
                                :is-edit="!!edit"
                                :is-new="isNewRecord"
                                :receivals="receivals"
                                @updateRecord="getTiaSamples"
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
                                        <a role="button" @click="deleteTiaSample(tiaSample?.id)">
                                            <span class="fa fa-trash-o"></span> Delete
                                        </a>
                                    </li>
                                    <li>
                                        <a role="button" @click="setEdit(tiaSample?.id)">
                                            <span class="fa fa-edit"></span>Edit
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-if="tiaSample">
                        <ol class="breadcrumb">
                            <li>
                                <Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link>
                            </li>
                            <li>
                                <Link href="" data-dismiss="modal" aria-label="Close">Tia Sample</Link>
                            </li>
                            <li class="active" v-if="isNewRecord">New</li>
                            <li class="active" v-else-if="tiaSample">{{ tiaSample?.receival?.user?.name }}</li>
                        </ol>
                        <Details
                            :tia-sample="tiaSample"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            :receivals="receivals"
                            @updateRecord="getTiaSamples"
                            col-size="col-md-12"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
