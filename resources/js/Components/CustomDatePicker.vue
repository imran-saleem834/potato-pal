<script setup>
import { watchEffect } from 'vue';
import { DatePicker } from 'v-calendar';

const props = defineProps({
  form: {
    required: true,
  },
  field: {
    type: String,
    required: true,
  },
  mode: {
    type: String,
    default: 'dateTime',
  },
  format: {
    type: String,
    default: 'YYYY-MM-DD HH:mm:ss',
  },
});

const emit = defineEmits(['update:modelValue']);

watchEffect(() => {
  emit('update:modelValue', props.form);
});
</script>

<template>
  <div class="position-relative p-0">
    <DatePicker
      v-model.string="form[field]"
      :mode="mode"
      :masks="{
        modelValue: props.format,
      }"
    >
      <template #default="{ togglePopover }">
        <input
          type="text"
          class="form-control"
          :class="{ 'is-invalid': form.errors[field] }"
          :value="form[field]"
          @click="togglePopover"
        />
        <div v-if="form.errors[field]" class="invalid-feedback" v-text="form.errors[field]" />
      </template>
    </DatePicker>
  </div>
</template>
