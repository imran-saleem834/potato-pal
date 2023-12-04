<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from "vue";
import moment from 'moment';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from "@/Pages/File/Details.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
import VueEasyLightbox from "vue-easy-lightbox";

const props = defineProps({
    files: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const flatFiles = ref(Object.values(props.files).flat(2));
const file = ref(flatFiles.value[0]);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);const visibleRef = ref(false);
const indexRef = ref(0);

watch(() => props.files,
    (newFiles) => {
        flatFiles.value = Object.values(newFiles).flat(2);
        const selectedFileId = edit.value ? file.value.id : flatFiles.value[0].id;
        file.value = flatFiles.value.find(f => f.id === selectedFileId);

        edit.value = null
        isNewRecord.value = false
    }
);

watch(search, (value) => {
    router.get(
        route('files.index'),
        { search: value },
        { preserveState: true, preserveScroll: true },
    )
});

const filter = (keyword) => search.value = keyword;

const setActiveTab = (id) => {
    file.value = flatFiles.value.find(f => f.id === id)
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

const showImg = () => {
    indexRef.value = 0;
    visibleRef.value = true;
};

const onHide = () => visibleRef.value = false;

const form = useForm({
    title: isNewRecord ? '' : file.value.title,
    image: '',
});

const deleteFile = (id) => {
    edit.value = null;
    form.delete(route('files.destroy', id), {
        preserveState: true,
    });
}
</script>

<template>
    <AppLayout title="Files">
        <TopBar
            type="Files"
            :value="search"
            @search="filter"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            type="Files"
            :title="file.title || 'New'"
            :is-edit-record-selected="!!edit"
            :is-new-record-selected="isNewRecord"
            @newRecord="setNewRecord"
            @editRecord="() => setEdit(file?.id)"
            @deleteRecord="() => deleteFile(file?.id)"
        />

        <!-- tab-section -->
        <div class="tab-section files-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
                    <div class="files-left">
                        <template v-for="(images, date) in files" :key="date">
                            <h5>{{ moment(date).format('DD, MMM YYYY') }}</h5>
                            <ul>
                                <li v-for="image in images" :key="image.id">
                                    <img
                                        style="cursor: pointer"
                                        :src="`storage/${image.image}`"
                                        :alt="image.title"
                                        :data-toggle="$windowWidth <= 767 ? 'modal' : 'tab'"
                                        :data-target="$windowWidth <= 767 ? '#user-details' : ''"
                                        @click="() => setActiveTab(image.id)"
                                    />
                                </li>
                            </ul>
                        </template>
                    </div>
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="slider-files hidden-xs">
                        <Details
                            :file="file"
                            :flat-files="flatFiles"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            @index="(index) => file = flatFiles[index]"
                            @update="() => {}"
                            @create="() => {}"
                            @showImg="showImg"
                        />
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <ModalHeader
                        title="Files"
                        :is-new="isNewRecord"
                        @edit="() => setEdit(file?.id)"
                        @delete="() => deleteFile(file?.id)"
                    />
                    <div class="modal-body">
                        <ModalBreadcrumb
                            page="Files"
                            :title="file?.title || 'File'"
                        />
                        <Details
                            :file="file"
                            :flat-files="flatFiles"
                            :is-edit="!!edit"
                            :is-new="isNewRecord"
                            @index="(index) => file = flatFiles[index]"
                            @update="() => {}"
                            @create="() => {}"
                            @showImg="showImg"
                        />
                    </div>
                </div>
            </div>
        </div>

        <vue-easy-lightbox
            :visible="visibleRef"
            :imgs="[`storage/${file.image}`]"
            :index="indexRef"
            @hide="onHide"
        />
    </AppLayout>
</template>
