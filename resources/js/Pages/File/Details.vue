<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import moment from 'moment';
import TextInput from "@/Components/TextInput.vue";

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

const isForm = computed(() => {
  return props.isEdit || props.isNew;
});

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
      emit('update')
    },
  });
};

const storeRecord = () => {
  form.post(route('files.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.image = null;
      emit('create')
    },
  });
};
</script>

<template>
  <template v-if="isForm">
    <TextInput v-model="form.title" :error="form.errors.title" type="text"/>

    <input type="file" @input="form.image = $event.target?.files[0] || ''"/>
    <div :class="{'has-error' : form.errors.image}">
            <span v-show="form.errors.image" class="help-block text-left">
                {{ form.errors.image }}
            </span>
    </div>

    <div class="flex-end">
      <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
      <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">Create</a>
    </div>
  </template>
  <template v-else>
    <div id="carousel-example-generic" class="slide" data-ride="carousel">
      <div class="carousel-inner" role="listbox">
        <div v-if="Object.values(file).length > 0" class="item active" @click="emit('showImg')" style="cursor: zoom-in">
          <img :src="`storage/${file.image}`" :alt="file.title"/>
          <div class="">
            <h4>{{ moment(file.created_at).format('DD, MMM YYYY hh:mm') }}</h4>
            <p>{{ file.title }}</p>
          </div>
        </div>
      </div>
      <a v-if="isPrev" class="left carousel-control" role="button" @click="prev">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a v-if="isNext" class="right carousel-control" role="button" @click="next">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
  </template>
</template>
