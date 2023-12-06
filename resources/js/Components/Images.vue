<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import VueEasyLightbox from "vue-easy-lightbox";

const props = defineProps({
    images: {
        type: Array,
        default: [],
    },
    uploadRoute: {
        type: String,
        default: null,
    },
    deleteRoute: {
        type: String,
        default: null,
    }
});

const emit = defineEmits(['updateRecord']);

const photoInput = ref(null);
const visibleRef = ref(false);
const indexRef = ref(0);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const showImg = (index) => {
    indexRef.value = index;
    visibleRef.value = true;
};

const onHide = () => visibleRef.value = false;

const updatePhotoPreview = () => {
    const formData = new FormData();
    formData.append('file', photoInput.value.files[0]);

    router.post(props.uploadRoute, formData, {
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            emit('updateRecord')
        },
    });
};

const deletePhoto = (image) => {
    router.post(props.deleteRoute, { image }, {
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
</script>

<template>
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
    <ul class="row tia-images">
        <li v-for="(image, index) in images" :key="image">
            <img :src="`storage/${image}`" :alt="image" @click="showImg(index)"/>
            <i class="fa fa-close" @click="deletePhoto(image)"></i>
        </li>
    </ul>
    <li
        v-if="!images || images.length <= 0"
        style="text-align: center; list-style: none; margin-bottom: 50px;"
    >No Records Found
    </li>
    <vue-easy-lightbox
        :visible="visibleRef"
        :imgs="images?.map(img => `storage/${img}`)"
        :index="indexRef"
        @hide="onHide"
    />
</template>
