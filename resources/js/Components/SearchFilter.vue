<script setup>
const props = defineProps({
  title: String,
  fields: {
    type: Array,
    default: [],
  },
});

const emit = defineEmits(['search']);

const searched = (keywords = '') => {
  emit('search', keywords);
  const modal = document.getElementById('search-filter');
  modal.querySelector('.btn-close').click();
}
</script>

<template>
  <div
    tabindex="-1"
    data-bs-scroll="true"
    id="search-filter"
    class="offcanvas offcanvas-end"
    aria-labelledby="search-filter-label"
  >
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="search-filter-label">{{ title }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="text-end">
        <a role="button" class="text-decoration-none" @click="searched()">Clear Filter</a>
      </div>
      
      <ul class="list-unstyled">
        <li v-for="field in fields" :key="field">
          <label class="form-label">{{ field.label }}</label>
          <div class="p-0 input-group mb-3">
            <template v-if="field?.options?.length > 0">
              <select class="form-control" @change="searched(`${field.name}:${$event.target.value}`)">
                <option value="">Select Option</option>
                <option 
                  v-for="option in field.options" 
                  :value="option.value" 
                  :key="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
            </template>
            <template v-else>
              <input
                type="text"
                class="form-control"
                @keyup.enter.prevent="searched(`${field.name}:${$event.target.value}`)"
              />
              <a href="javascript:;" class="input-group-text">
                <i class="bi bi-search"></i>
              </a>
            </template>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>
