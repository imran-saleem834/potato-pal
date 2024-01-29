<script setup>
import { onMounted, ref } from 'vue';

defineProps({
  modelValue: String | Number,
  type: {
    type: String,
    default: 'text',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
  if (input.value.hasAttribute('autofocus')) {
    input.value.focus();
  }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
  <div class="p-0" :class="{ 'input-group' : $slots.addon, 'is-invalid' : error }">
    <input
      ref="input"
      :type="type"
      class="form-control"
      :class="{'is-invalid' : error}"
      :disabled="disabled"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    >
    <slot name="addon"/>
  </div>
  <div v-if="error" class="invalid-feedback">{{ error }}</div>
</template>
