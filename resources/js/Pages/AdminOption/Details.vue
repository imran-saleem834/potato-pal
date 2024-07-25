<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';

const toast = useToast();

const props = defineProps({
  category: {
    type: Object,
    default: {},
  },
  type: String,
  isNew: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
  name: props.category.name,
  type: props.type,
});

const edit = ref(false);

watch(
  () => props.category,
  (category) => {
    form.clearErrors();
    form.category = category.name;
  },
);

const editRecord = () => {
  edit.value = props.category.id;
};

const updateRecord = () => {
  form.patch(route('categories.update', props.category.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      edit.value = false;
      toast.success('The option has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('categories.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('updateRecord');
      toast.success('The option has been created successfully!');
    },
  });
};

const deleteRecord = () => {
  form.delete(route('categories.destroy', props.category.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      edit.value = false;
      toast.success('The option has been deleted successfully!');
    },
  });
};
</script>

<template>
  <div class="user-boxes">
    <table class="table input-table">
      <tr>
        <th>Name</th>
        <td>
          <TextInput v-if="edit || isNew" v-model="form.name" :error="form.errors.name" type="text" />
          <template v-else>
            <span class="fw-bold">{{ category.name }}</span>
          </template>
        </td>
      </tr>
    </table>

    <ul class="mt-4">
      <li v-if="isNew">
        <button
          data-bs-toggle="modal"
          data-bs-target="#store-admin-option-record"
          class="btn btn-red"
          :disabled="form.processing"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else>Create</template>
        </button>
      </li>
      <li v-if="!isNew && !edit">
        <button @click="editRecord" class="btn btn-red">Edit</button>
      </li>
      <li v-if="edit">
        <button
          data-bs-toggle="modal"
          :data-bs-target="`#update-admin-option-record-${category.id}`"
          class="btn btn-red"
          :disabled="form.processing"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else>Update</template>
        </button>
      </li>
      <li v-if="!isNew">
        <button
          data-bs-toggle="modal"
          :data-bs-target="`#delete-admin-option-record-${category.id}`"
          class="btn btn-red"
          :disabled="form.processing"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else>Delete</template>
        </button>
      </li>
    </ul>
  </div>

  <ConfirmedModal
    v-if="edit"
    :id="`update-admin-option-record-${category.id}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />

  <ConfirmedModal
    v-if="isNew"
    id="store-admin-option-record"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    v-if="!isNew"
    :id="`delete-admin-option-record-${category.id}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteRecord"
  />
</template>
