<script setup>
import Pagination from '@/Components/Pagination.vue';

defineProps({
  items: Array,
  links: {
    type: Array,
    default: [],
  },
  activeTab: {
    type: Number,
    default: null,
  },
  row1: Object,
  row2: Object,
});

const emit = defineEmits(['click']);

const menuClick = (id) => {
  emit('click', id);
};

const getObjectProperty = (obj, key) => {
  const keys = key.split('.');
  let result = obj;

  for (const k of keys) {
    if (result && result.hasOwnProperty(k)) {
      result = result[k];
    } else {
      // Property not found
      return undefined;
    }
  }

  return result;
};
</script>

<template>
  <a
    role="button"
    v-for="item in items"
    :key="item.id"
    class="w-100 d-block position-relative"
    :class="{ active: activeTab === item.id }"
    @click="menuClick(item.id)"
  >
    <table class="table table-borderless mb-0">
      <tbody>
        <tr>
          <th v-text="row1.title" />
          <th v-text="row2.title" />
        </tr>
        <tr>
          <td v-text="getObjectProperty(item, row1.value)" />
          <td v-text="getObjectProperty(item, row2.value)" />
        </tr>
      </tbody>
    </table>
    <i class="bi bi-chevron-right angle-right"></i>
  </a>
  <div v-if="items.length <= 0" class="text-center" style="margin-top: calc(50vh - 120px)">No Records Found</div>
  <div v-if="links.length > 0" class="float-end mt-3 me-2">
    <Pagination :links="links" />
  </div>
</template>
