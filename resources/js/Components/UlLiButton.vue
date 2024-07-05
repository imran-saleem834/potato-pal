<script setup>
import { computed } from 'vue';

defineEmits(['click']);

const props = defineProps({
  isForm: {
    type: Boolean,
    default: false,
  },
  items: {
    type: Array,
    default: [],
  },
  error: {
    type: String,
    default: null,
  },
  value: {
    type: [Number, String, Boolean],
    default: null,
  },
  style: {
    default: null,
  },
});

const clickable = computed(() => {
  return props.isForm ? 'btn' : 'btn not-clickable';
});
</script>

<template>
  <ul class="p-0" :class="{ 'is-invalid': error }" :style="style">
    <li v-for="item in items" :key="item.value">
      <button
        @click="isForm && $emit('click', item.value)"
        :class="value === item.value ? `${clickable} btn-black` : `${clickable} btn-dark`"
      >
        {{ item.label }}
      </button>
    </li>
  </ul>
  <div v-if="error" class="invalid-feedback p-0 m-0" v-text="error"></div>
</template>
