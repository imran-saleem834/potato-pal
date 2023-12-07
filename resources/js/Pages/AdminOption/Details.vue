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
    <h4>Name</h4>
    <TextInput v-if="edit || isNew" v-model="form.name" :error="form.errors.name" type="text"/>
    <h5 style="margin-bottom: 40px;" v-else>{{ category.name }}</h5>

    <ul>
      <li v-if="isNew">
        <a role="button" @click="storeRecord" class="red-btn"><span class="fa fa-edit hidden"></span> Create</a>
      </li>
      <li v-if="!isNew && !edit">
        <a role="button" @click="editRecord" class="red-btn"><span class="fa fa-edit hidden"></span> Edit</a>
      </li>
      <li v-if="edit">
        <a role="button" @click="updateRecord" class="red-btn">
          <span class="fa fa-edit hidden"></span> Update
        </a>
      </li>
      <li class="hidden" v-if="!isNew">
        <a role="button" @click="deleteRecord" class="trash-btn"><span class="fa fa-trash-o"></span></a>
      </li>
      <li v-if="!isNew"><a role="button" @click="deleteRecord" class="red-btn">Delete</a></li>
    </ul>
  </div>
</template>
