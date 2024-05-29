<script setup>
import { computed, watch } from 'vue';
import { binSizes } from '@/const.js';
import { useForm } from "@inertiajs/vue3";
import TextInput from '@/Components/TextInput.vue';
import UlLiButton from '@/Components/UlLiButton.vue';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  dispatch: {
    type: Object,
    default: null
  },
});

const emit = defineEmits(['close']);

const form = useForm({
  dispatch: props.dispatch,
  bin_size: '',
  no_of_bins: '',
});

const storeRecord = () => {
  form.post(route('returns.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      const modal = document.getElementById(`returns-modal`);
      modal.querySelector('.btn-close').click();
      emit('close');
    },
  });
};

const allocation = computed(() => {
  if (props.dispatch.type === 'reallocation') {
    return props.dispatch.item.foreignable.item.foreignable;
  }
  return props.dispatch.item.foreignable;
});

watch(
  () => props.dispatch,
  (dispatch) => {
    if (dispatch) {
      form.dispatch = dispatch;
    }
  });
</script>

<template>
  <div class="modal fade" id="returns-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="returns-modal-Label">Return Allocation</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="$emit('close')"
          ></button>
        </div>
        <div v-if="form.dispatch && dispatch" class="modal-body tab-section">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
              <tr>
                <th>From</th>
                <th>Grower Group</th>
                <th>Grower</th>
                <th>Paddock</th>
                <th>Variety</th>
                <th>Gen</th>
                <th>Seed type</th>
                <th>Class</th>
                <th>Bin size</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>{{ dispatch.type.toUpperCase() }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-' }}</td>
                <td>{{ allocation.grower?.grower_name || '-' }}</td>
                <td>{{ allocation.paddock }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}</td>
                <td>{{ getBinSizesValue(dispatch.item.bin_size) }}</td>
              </tr>
              </tbody>
            </table>
          </div>
          <div class="row my-3">
            <div class="col-12 col-xl-4 mb-2">
              <div class="user-boxes p-0 m-0 shadow-none">
                <label class="form-label">Return Bin Size</label>
                <UlLiButton
                  :is-form="true"
                  :value="form.bin_size"
                  :error="form.errors.bin_size"
                  :items="binSizes"
                  @click="(key) => (form.bin_size = key)"
                />
              </div>
            </div>
            <div class="col-12 col-xl-2 mb-2">
              <label class="form-label">Return no of bins</label>
              <TextInput
                v-model="form.no_of_bins"
                :error="form.errors.no_of_bins"
                type="text"
              />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-red" @click="storeRecord">Save Return</button>
        </div>
      </div>
    </div>
  </div>
</template>
