<script setup>
const emit = defineEmits(['click']);

const props = defineProps({
  items: {
    type: Array,
    default: [],
  },
  error: {
    type: String,
    default: null,
  },
  value: {
    type: [Number, String],
    default: null,
  },
});
</script>

<template>
  <ul class="p-0" :class="{'is-invalid' : error}">
    <li v-for="item in items" :key="item.value">
      <a
        role="button"
        @click="$emit('click', item.value)"
        :class="{'btn-black' : value === item.value}"
      >{{ item.label }}</a>
    </li>
  </ul>
  <div v-if="error" class="invalid-feedback p-0 m-0" v-text="error"></div>
</template>
