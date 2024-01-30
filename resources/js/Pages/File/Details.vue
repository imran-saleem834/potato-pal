<script setup>
import moment from 'moment';
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import TextInput from "@/Components/TextInput.vue";

const toast = useToast();

const props = defineProps({
  file: Object,
  flatFiles: Array,
  isEdit: Boolean,
  isNew: Boolean,
});

const emit = defineEmits(['update', 'create', 'index', 'showImg']);

const form = useForm({
  title: props.file.title,
  image: null,
});

const next = () => {
  const index = props.flatFiles.findIndex((f) => f.id === props.file.id);
  emit('index', index + 1);
};

const prev = () => {
  const index = props.flatFiles.findIndex((f) => f.id === props.file.id);
  emit('index', index - 1);
};

const isNext = computed(() => {
  const index = props.flatFiles.findIndex((f) => f.id === props.file.id);
  return !!props.flatFiles[index + 1];
})

const isPrev = computed(() => {
  const index = props.flatFiles.findIndex((f) => f.id === props.file.id);
  return !!props.flatFiles[index - 1];
})

const isForm = computed(() => props.isEdit || props.isNew);

watch(() => props.file,
  (file) => {
    form.clearErrors();
    form.title = file.title
  }
);

watch(() => props.isNew,
  (isNew) => {
    if (isNew) {
      form.clearErrors();
      form.title = '';
    }
  }
);

const updateRecord = () => {
  form.patch(route('files.update', props.file.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The file has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('files.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.image = null;
      emit('create');
      toast.success('The file has been created successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord
});
</script>

<template>
  <template v-if="isForm">
    <div class="user-boxes">
      <table class="table text-start">
        <tr>
          <th>Title</th>
          <td><TextInput v-model="form.title" :error="form.errors.title" type="text"/></td>
        </tr>
        <tr>
          <th>File</th>
          <td>
            <input
              type="file"
              id="select-file"
              class="form-control"
              :class="{'is-invalid' : form.errors.image}"
              @input="form.image = $event.target?.files[0] || ''"
            />
            <div v-if="form.errors.image" class="invalid-feedback">{{ form.errors.image }}</div>
          </td>
        </tr>
      </table>
    </div>
  </template>
  <template v-else>
    <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div v-if="Object.values(file).length > 0" class="carousel-item active" @click="emit('showImg')">
          <img :src="`storage/${file.image}`" :alt="file.title" style="cursor: zoom-in" />
          <h5 class="mt-3">{{ moment(file.created_at).format('DD, MMM YYYY hh:mm') }}</h5>
          <p>{{ file.title }}</p>
        </div>
      </div>
      <button
        v-if="isPrev"
        class="carousel-control-prev"
        type="button"
        data-bs-target="#carouselExampleControls"
        data-bs-slide="prev"
        @click="prev"
      >
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button
        v-if="isNext"
        class="carousel-control-next"
        type="button"
        data-bs-target="#carouselExampleControls"
        data-bs-slide="next"
        @click="next"
      >
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </template>
</template>
