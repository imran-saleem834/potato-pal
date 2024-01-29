<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
  category: Object,
  type: String,
  isNew: Boolean,
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
  name: props.category.name,
  type: props.type,
});

const edit = ref(false);

watch(() => props.category,
  (category) => {
    form.clearErrors();
    form.category = category.name
  }
);

const editRecord = () => {
  edit.value = props.category.id;
}

const updateRecord = () => {
  form.patch(route('categories.update', props.category.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      edit.value = false
    },
  });
}

const storeRecord = () => {
  form.post(route('categories.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('updateRecord')
    },
  });
}
const deleteRecord = () => {
  form.delete(route('categories.destroy', props.category.id), {
    preserveScroll: true,
    preserveState: true,
  });
}
</script>

<template>
  <div class="user-boxes">
    <label class="form-label">Name</label>
    <TextInput v-if="edit || isNew" v-model="form.name" :error="form.errors.name" type="text"/>
    <h6 v-else class="fw-bold">{{ category.name }}</h6>

    <ul class="mt-4">
      <li v-if="isNew">
        <a role="button" @click="storeRecord" class="btn btn-red">Create</a>
      </li>
      <li v-if="!isNew && !edit">
        <a role="button" @click="editRecord" class="btn btn-red">Edit</a>
      </li>
      <li v-if="edit">
        <a role="button" @click="updateRecord" class="btn btn-red">Update</a>
      </li>
      <li v-if="!isNew"><a role="button" @click="deleteRecord" class="btn btn-red">Delete</a></li>
    </ul>
  </div>
</template>
