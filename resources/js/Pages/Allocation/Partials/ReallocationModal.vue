<script setup>
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import { getSingleCategoryNameByType, getBinSizesValue } from '@/helper.js';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  allocation: {
    type: Object,
    default: {},
  },
});

const emit = defineEmits(['close']);

const form = useForm({
  allocation: props.allocation,
  buyer_id: null,
  no_of_bins: null,
  weight: null,
});

const storeRecord = () => {
  form.post(route('allocation.duplicate', props.allocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      const modal = document.getElementById(props.id);
      modal.querySelector('.btn-close').click();
      emit('close');
    },
  });
};
</script>

<template>
  <div class="modal fade" :id="id" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 v-if="Object.values(allocation).length > 0" class="modal-title" :id="`${id}-label`">
            {{ $page.props.buyers.find(buyer => buyer.value === allocation.buyer_id).label }}
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="$emit('close')"
          ></button>
        </div>
        <div v-if="Object.values(allocation).length > 0" class="modal-body">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Grower Group</th>
                  <th>Grower</th>
                  <th>Paddock</th>
                  <th>Variety</th>
                  <th>Gen</th>
                  <th>Seed type</th>
                  <th>Class</th>
                  <th>Bin Size</th>
                  <th>No of bins</th>
                  <th>Weight</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-' }}</td>
                  <td>{{ allocation.grower?.grower_name || '-' }}</td>
                  <td>{{ allocation.paddock }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}</td>
                  <td>{{ getBinSizesValue(allocation.item.bin_size) }}</td>
                  <td>{{ allocation.item.no_of_bins }}</td>
                  <td>{{ allocation.item.weight }} kg</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row my-3">
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <label class="form-label">Buyer to allocate</label>
              <Multiselect
                v-model="form.buyer_id"
                mode="single"
                placeholder="Choose a buyer"
                :searchable="true"
                :options="$page.props.buyers"
                :class="{ 'is-invalid': form.errors.buyer_id }"
              />
              <div
                v-if="form.errors.buyer_id"
                class="invalid-feedback p-0 m-0"
                v-text="form.errors.buyer_id"
              />
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <label class="form-label">No of bins</label>
              <TextInput type="text" v-model="form.no_of_bins" :error="form.errors.no_of_bins" />
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <label class="form-label">Weight</label>
              <TextInput type="text" v-model="form.weight" :error="form.errors.weight">
                <template #addon>
                  <div class="input-group-text">kg</div>
                </template>
              </TextInput>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="emit('close')">Close</button>
          <button type="button" class="btn btn-red" @click="storeRecord">Reallocate</button>
        </div>
      </div>
    </div>
  </div>
</template>
