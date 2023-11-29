<script setup>
import { computed, ref, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import VueEasyLightbox from 'vue-easy-lightbox';

const props = defineProps({
    note: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
    title: props.note.title,
    note: props.note.note,
    tags: props.note.tags,
});

watch(() => props.note,
    (note) => {
        form.clearErrors();
        form.title = note.title
        form.note = note.note
        form.tags = note.tags
    }
);

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const photoInput = ref(null);
const visibleRef = ref(false)
const indexRef = ref(0)

const showImg = (index) => {
    indexRef.value = index
    visibleRef.value = true
}

const onHide = () => visibleRef.value = false

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const formData = new FormData();
    formData.append('file', photoInput.value.files[0]);

    router.post(route('notes.upload', props.note.id), formData, {
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            emit('updateRecord')
        },
    });
};

const deletePhoto = (image) => {
    router.post(route('notes.delete', props.note.id), { image }, {
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            emit('updateRecord')
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

const updateRecord = () => {
    form.patch(route('notes.update', props.note.id), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const storeRecord = () => {
    form.post(route('notes.store'), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}
</script>

<template>
    <div class="row">
        <div v-if="isEdit || isNew" class="col-md-12">
            <div class="flex-end create-update-btn">
            <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">
                <span class="fa fa-edit"></span> Update
            </a>
            <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">
                <span class="fa fa-edit"></span> Create
            </a>
            </div>
        </div>
        <div :class="colSize">
            <div class="user-boxes">
                <h6>Title</h6>
                <TextInput v-if="isForm" v-model="form.title" :error="form.errors.title" type="text"/>
                <h5 v-else>{{ note.title }}</h5>

                <h6>Note</h6>
                <div v-if="isForm" class="form-group" :class="{'has-error' : form.errors.note}">
                    <textarea v-model="form.note" class="form-control" rows="3"></textarea>
                    <span v-show="form.errors.note" class="help-block text-left">{{ form.errors.note }}</span>
                </div>
                <h5 v-else>{{ note.note }}</h5>

                <h6>Unique Tags</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.tags"
                    mode="tags"
                    placeholder="Choose a tags"
                    :searchable="true"
                    :create-option="true"
                    :options="form.tags"
                />
                <ul v-else-if="note.tags">
                    <li v-for="tag in note.tags" :key="tag"><a>{{ tag }}</a></li>
                </ul>
            </div>
        </div>
        <div :class="colSize">
            <h4 v-if="!isNew">Images</h4>
            <div v-if="!isNew" class="user-boxes">
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >
                <div class="user-column-two">
                    <div>&nbsp;</div>
                    <div>
                        <button class="btn-red" type="button" @click="selectNewPhoto">+ Add</button>
                    </div>
                </div>

                <div class="row tia-images">
                    <div v-for="(image, index) in note.images" :key="image" class="col-xs-12 col-sm-4">
                        <img :src="`storage/${image}`" :alt="image" @click="showImg(index)"/>
                        <i class="fa fa-close" @click="deletePhoto(image)"></i>
                    </div>
                </div>
                <vue-easy-lightbox :visible="visibleRef" :imgs="note?.images?.map(img => `storage/${img}`)" :index="indexRef" @hide="onHide"></vue-easy-lightbox>
            </div>
        </div>
    </div>
</template>
