<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import Images from "@/Components/Images.vue";

const props = defineProps({
  note: Object,
  isEdit: Boolean,
  isNew: Boolean,
});

const emit = defineEmits(['update', 'create']);

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

const isForm = computed(() => props.isEdit || props.isNew)

const updateRecord = () => {
  form.patch(route('notes.update', props.note.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update')
    },
  });
}

const storeRecord = () => {
  form.post(route('notes.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create')
    },
  });
}
</script>

<template>
  <div v-if="isForm" class="d-flex justify-content-end mb-2">
    <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
    <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">Create</a>
  </div>
  <div class="row">
    <div class="col-12">
      <div v-if="isForm" class="user-boxes">
        <label class="form-label">Title</label>
        <TextInput v-model="form.title" :error="form.errors.title" type="text"/>

        <label class="form-label mt-3">Note</label>
        <textarea
          rows="5"
          v-model="form.note"
          class="form-control"
          :class="{'is-invalid' : form.errors.note}"
        ></textarea>
        <div v-if="form.errors.note" class="invalid-feedback">{{ form.errors.note }}</div>

        <label class="form-label mt-3">Unique Tags</label>
        <Multiselect
          v-model="form.tags"
          mode="tags"
          placeholder="Choose a tags"
          :searchable="true"
          :create-option="true"
          :options="form.tags"
        />
        <div v-if="form.errors.tags" class="invalid-feedback" v-text="form.errors.tags"/>
        <div v-if="form.errors['tags.0']" class="invalid-feedback" v-text="form.errors['tags.0']"/>
        <div v-if="form.errors['tags.1']" class="invalid-feedback" v-text="form.errors['tags.1']"/>
      </div>

      <div v-if="!isNew" class="user-boxes notes-list">
        <p v-if="!isForm">{{ note.note }}</p>

        <Images
          v-if="!isNew"
          :images="note?.images"
          :upload-route="route('notes.upload', note?.id || 0)"
          :delete-route="route('notes.delete', note?.id || 0)"
          @updateRecord="emit('update')"
        />
      </div>
    </div>
  </div>
</template>
