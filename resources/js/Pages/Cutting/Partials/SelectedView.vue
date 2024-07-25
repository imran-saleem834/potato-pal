<script setup>
import { computed, watchEffect } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import { toTonnes, getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  loader: Boolean,
  selected: Object,
  form: {
    required: true,
  },
});

const emit = defineEmits(['update:modelValue']);

const allocation = computed(() => {
  if (props.selected.type === 'sizing') {
    return props.selected.allocatable.sizeable;
  }
  return props.selected;
});

watchEffect(() => {
  emit('update:modelValue', props.form);
});
</script>

<template>
  <div v-if="loader" class="d-flex justify-content-center my-3">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
  <div v-if="!loader && Object.values(selected).length > 0" class="table-responsive">
    <table class="table table-sm align-middle mb-3">
      <thead>
        <tr>
          <th class="d-none d-md-table-cell">Grower</th>
          <th class="d-none d-md-table-cell">Paddock</th>
          <th class="d-none d-md-table-cell">Variety</th>
          <th class="d-none d-md-table-cell">Gen.</th>
          <th>Seed type</th>
          <th class="d-none d-md-table-cell">Class</th>
          <template v-if="selected.type === 'sizing'">
            <th>Half tonnes</th>
            <th>One tonnes</th>
            <th>Two tonnes</th>
          </template>
          <template v-else>
            <th>Bin size</th>
            <th>Weight</th>
            <th>Available/No of bins</th>
            <th>Bins to cut</th>
          </template>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="d-none d-md-table-cell text-primary">{{ allocation.grower.grower_name }}</td>
          <td class="d-none d-md-table-cell text-primary">{{ allocation.paddock }}</td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}
          </td>
          <td class="text-primary">
            <template v-if="selected.type === 'sizing'">
              {{ getSingleCategoryNameByType(selected.categories, 'seed-type') || '-' }}
            </template>
            <template v-else>
              {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
            </template>
            <a
              data-bs-toggle="tooltip"
              data-bs-html="true"
              class="d-md-none"
              :data-bs-title="`
              <div class='text-start'>
                Grower: ${allocation.grower.grower_name}<br/>
                Paddock: ${allocation.paddock}<br/>
                Variety: ${getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-'}<br/>
                Gen.: ${getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-'}<br/>
                Class: ${getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-'}
              </div>
            `"
            >
              <i class="bi bi-question-circle fs-6 text-black"></i>
            </a>
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}
          </td>
          <template v-if="selected.type === 'sizing'">
            <td class="text-primary">{{ `${selected.available_half_tonnes} Bins` }}</td>
            <td class="text-primary">{{ `${selected.available_one_tonnes} Bins` }}</td>
            <td class="text-primary">{{ `${selected.available_two_tonnes} Bins` }}</td>
          </template>
          <template v-else>
            <td class="text-primary">{{ getBinSizesValue(allocation.item.bin_size) }}</td>
            <td class="text-primary">{{ toTonnes(allocation.item.weight) }}</td>
            <td class="text-primary">{{ allocation.available_no_of_bins }}</td>
            <td style="max-width: 150px">
              <TextInput
                v-model="form.selected_allocation.no_of_bins"
                :error="form.errors[`selected_allocation.no_of_bins`]"
                type="text"
              />
            </td>
          </template>
        </tr>
      </tbody>
    </table>
  </div>
</template>
