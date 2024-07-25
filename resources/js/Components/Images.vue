<script setup>
import { computed, ref } from 'vue';
import VueEasyLightbox from 'vue-easy-lightbox';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
  images: {
    type: Array,
    default: [],
  },
  type: {
    type: String,
    required: true,
  },
  id: {
    type: Number,
    required: true,
  },
  field: {
    type: String,
    default: 'images',
  },
});

const emit = defineEmits(['update']);

const photoInput = ref(null);
const visibleRef = ref(false);
const indexRef = ref(0);
const keywords = ref(null);
const files = ref(0);

const form = useForm({
  images: [],
  field: props.field,
});

const selectNewPhoto = () => {
  photoInput.value.click();
};

const showImg = (index) => {
  indexRef.value = index;
  visibleRef.value = true;
};

const onHide = () => (visibleRef.value = false);

const updatePhotoPreview = () => {
  const formData = new FormData();
  formData.append('file', photoInput.value.files[0]);
  formData.append('field', props.field);

  router.post(route('media.upload', [props.type, props.id]), formData, {
    preserveScroll: true,
    onSuccess: () => {
      clearPhotoFileInput();
      emit('update');
    },
  });
};

const deletePhoto = (image) => {
  router.post(
    route('media.delete', [props.type, props.id]),
    { image, field: props.field },
    {
      preserveScroll: true,
      onSuccess: () => {
        clearPhotoFileInput();
        emit('update');
      },
    },
  );
};

const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }
};

const arrayImages = computed(() => Array.from({ length: props.images.length + 1 }, (x, i) => i));

const getFiles = (routeUrl = null) => {
  if (!routeUrl) {
    routeUrl = route('media.files', { search: keywords.value });
  }
  axios.get(routeUrl).then((response) => {
    files.value = response.data.files;
    keywords.value = response.data.filters.search;
  });
};

const attachImages = () => {
  form.post(route('media.attach', [props.type, props.id]), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      emit('update');
    },
  });
};
</script>

<template>
  <div class="d-flex justify-content-end">
    <button
      type="button"
      class="btn btn-red"
      data-bs-toggle="modal"
      data-bs-target="#existing-files"
      @click="() => getFiles()"
    >
      + Attach from files
    </button>
  </div>

  <div class="d-flex flex-wrap">
    <div v-for="i in arrayImages" :key="i" class="upload-btn-wrapper m-2">
      <template v-if="images[i]">
        <img :alt="i" :src="`/storage/${images[i]}`" @click="showImg(i)" />
        <i v-if="images[i]" class="bi bi-trash" @click="deletePhoto(images[i])"></i>
      </template>
      <button v-else class="btn" @click="selectNewPhoto">
        <img :alt="i" src="/images/icon-file.png" />
      </button>
    </div>
  </div>

  <div class="upload-btn-wrapper">
    <input id="photo" ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview" />
  </div>

  <vue-easy-lightbox
    :visible="visibleRef"
    :imgs="images?.map((img) => `/storage/${img}`)"
    :index="indexRef"
    @hide="onHide"
  />

  <div class="modal fade" id="existing-files" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Files</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div v-if="files" class="modal-body">
          <div class="tab-section">
            <div class="user-boxes m-0 p-0 shadow-none">
              <div class="form-group position-relative">
                <i class="bi bi-search form-control-feedback"></i>
                <input
                  type="text"
                  class="form-control custom-input"
                  v-model="keywords"
                  @input="() => getFiles()"
                  placeholder="Search"
                />
              </div>
              <div class="d-flex flex-wrap">
                <div v-for="file in files.data" :key="file.id" class="upload-btn-wrapper m-2">
                  <label class="d-block mb-2">
                    <input type="checkbox" v-model="form.images" :value="file.image" class="me-1" />
                    {{ file.title }}
                  </label>
                  <img :alt="file.id" :src="`/storage/${file.image}`" />
                </div>
              </div>
              <div v-if="files.links.length > 3" class="float-end mt-1 me-1">
                <nav aria-label="Page navigation example">
                  <ul class="pagination">
                    <template v-for="(link, key) in files.links" :key="key">
                      <li v-if="link.url" class="page-item">
                        <a
                          class="page-link text-black"
                          :class="{ 'btn-red text-white': link.active }"
                          @click="getFiles(link.url)"
                          v-html="link.label.replace('Previous', '').replace('Next', '')"
                        />
                      </li>
                    </template>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-red" data-bs-dismiss="modal" @click="attachImages">Attach</button>
        </div>
      </div>
    </div>
  </div>
</template>
