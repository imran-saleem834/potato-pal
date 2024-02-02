<script setup>
import moment from 'moment';
import Pagination from "@/Components/Pagination.vue";

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
    if (result.hasOwnProperty(k)) {
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
    :class="{'active' : activeTab === item.id}"
    @click="menuClick(item.id)"
  >
    <table class="table table-borderless mb-0">
      <tbody>
      <tr>
        <th>{{ moment(item.created_at).format('DD, MMM YYYY') }}</th>
        <th></th>
      </tr>
      <tr>
        <td v-text="getObjectProperty(item, row1.value)"/>
        <td class="d-flex justify-content-center">
          <template v-if="item.tags">
            <span v-for="tag in item.tags" :key="tag" class="btn btn-red shadow-none mx-1">{{ tag }}</span>
          </template>
        </td>
      </tr>
      </tbody>
    </table>
    <i class="bi bi-chevron-right angle-right"></i>
  </a>
  <div
    v-if="items.length <= 0"
    class="text-center"
    style="margin-top: calc(50vh - 120px);"
  >No Records Found
  </div>
  <div v-if="links.length > 0" class="float-end mt-3 me-2">
    <Pagination :links="links"/>
  </div>
</template>
