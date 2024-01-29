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
    class="d-none"
    @change="updatePhotoPreview"
  >
  <div class="d-flex justify-content-end">
    <button class="btn btn-red" type="button" @click="selectNewPhoto">+ Add</button>
  </div>
  <ul class="m-0 p-0">
    <li class="me-4 mt-3 position-relative" v-for="(image, index) in images" :key="image">
      <img :src="`storage/${image}`" :alt="image" @click="showImg(index)"/>
      <i class="bi bi-x-circle" @click="deletePhoto(image)"></i>
    </li>
  </ul>
  <li
    v-if="!images || images.length <= 0"
    class="text-center"
    style="list-style: none; margin-bottom: 50px;"
  >No Files Found
  </li>
  <vue-easy-lightbox
    :visible="visibleRef"
    :imgs="images?.map(img => `storage/${img}`)"
    :index="indexRef"
    @hide="onHide"
  />
</template>
