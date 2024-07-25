<script setup>
import { computed, watch } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import Images from '@/Components/Images.vue';

const { errors } = usePage().props;
const toast = useToast();

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

watch(
  () => props.note,
  (note) => {
    form.clearErrors();
    form.title = note.title;
    form.note = note.note;
    form.tags = note.tags;
  },
);

const isForm = computed(() => props.isEdit || props.isNew);

const updateRecord = () => {
  form.patch(route('notes.update', props.note.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The note has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('notes.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The note has been created successfully!');
    },
  });
};

const hasArrayErrors = (arrayName) => {
  return computed(() => {
    return Object.keys(errors).some((key) => key.startsWith(`${arrayName}.`));
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});
</script>

<template>
  <div class="row">
    <div class="col-12">
      <div v-if="isForm" class="user-boxes">
        <table class="table input-table">
          <tr>
            <th>Title</th>
            <td>
              <TextInput v-model="form.title" :error="form.errors.title" type="text" />
            </td>
          </tr>
          <tr>
            <th>Note</th>
            <td>
              <textarea
                rows="5"
                v-model="form.note"
                class="form-control"
                :class="{ 'is-invalid': form.errors.note }"
              ></textarea>
              <div v-if="form.errors.note" class="invalid-feedback">{{ form.errors.note }}</div>
            </td>
          </tr>
          <tr>
            <th>Unique Tags</th>
            <td>
              <Multiselect
                v-model="form.tags"
                mode="tags"
                placeholder="Choose a tags"
                :searchable="true"
                :create-option="true"
                :class="{ 'is-invalid': hasArrayErrors('tags') }"
              />
              <div v-if="form.errors.tags" class="invalid-feedback" v-text="form.errors.tags" />
              <div v-if="form.errors['tags.0']" class="invalid-feedback" v-text="form.errors['tags.0']" />
              <div v-if="form.errors['tags.1']" class="invalid-feedback" v-text="form.errors['tags.1']" />
            </td>
          </tr>
        </table>
      </div>

      <div v-if="!isNew" class="user-boxes notes-list">
        <p v-if="!isForm">{{ note.note }}</p>

        <hr v-if="!isForm" />

        <Images v-if="!isNew" type="notes" :id="note?.id || 0" :images="note?.images || []" @update="emit('update')" />
      </div>
    </div>
  </div>
</template>
