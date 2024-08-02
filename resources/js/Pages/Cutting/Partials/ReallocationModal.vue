<script setup>
import { computed, watch } from "vue";
import { useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import { getSingleCategoryNameByType } from '@/helper.js';

const toast = useToast();

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  cutting: {
    type: Object,
    default: {},
  },
});

const isSizing = computed(() => props.cutting.type === 'sizing');
const allocation = computed(() => {
  if (isSizing.value) {
    return props.cutting.item.foreignable.allocatable.sizeable;
  }
  return props.cutting.item.foreignable;
});
const seedTypeCategories = computed(() => props.cutting.item.foreignable.categories);

const emit = defineEmits(['close']);

const form = useForm({
  selected_cutting: props.cutting,
  allocation_buyer_id: props.cutting.buyer_id,
  buyer_id: null,
  half_tonnes: null,
  one_tonnes: null,
  two_tonnes: null,
  comment: null,
  returns: true,
});

watch(
  () => props.cutting,
  (cutting) => {
    if (Object.values(cutting).length > 0) {
      form.selected_cutting = cutting;
      form.allocation_buyer_id = cutting.buyer_id;
    }
  },
);

const storeRecord = () => {
  form.post(route('reallocations.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      const modal = document.getElementById(props.id);
      modal.querySelector('.btn-close').click();
      emit('close');

      toast.success('The reallocation has been created successfully!');
    },
  });
};
</script>

<template>
  <div class="modal fade" :id="id" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 v-if="Object.values(cutting).length > 0" class="modal-title" :id="`${id}-label`">
            {{ $page.props.buyers.find((buyer) => buyer.value === allocation.buyer_id).label }}
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="$emit('close')"
          ></button>
        </div>
        <div v-if="Object.values(cutting).length > 0" class="modal-body">
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
                  <th>Half Tonnes</th>
                  <th>One Tonnes</th>
                  <th>Two Tonnes</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-' }}</td>
                  <td>{{ allocation.grower?.grower_name || '-' }}</td>
                  <td>{{ allocation.paddock }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(seedTypeCategories, 'seed-type') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}</td>
                  <td>{{ cutting.item.half_tonnes || '0' }} Bins</td>
                  <td>{{ cutting.item.one_tonnes || '0' }} Bins</td>
                  <td>{{ cutting.item.two_tonnes || '0' }} Bins</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row my-3">
            <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 mb-3">
              <label class="form-label">Buyer to allocate</label>
              <Multiselect
                v-model="form.buyer_id"
                mode="single"
                placeholder="Choose a buyer"
                :searchable="true"
                :options="$page.props.buyers"
                :class="{ 'is-invalid': form.errors.buyer_id }"
              />
              <div v-if="form.errors.buyer_id" class="invalid-feedback p-0 m-0" v-text="form.errors.buyer_id" />
            </div>
            <div class="col-12 col-sm-6 col-md-8 col-lg-8 col-xl-8 mb-3">
              <label class="form-label">Comments</label>
              <TextInput v-model="form.comment" :error="form.errors.comment" type="text" />
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
              <TextInput type="text" v-model="form.half_tonnes" :error="form.errors.half_tonnes">
                <template #prefix-addon>
                  <div class="input-group-text">Half tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
              <TextInput type="text" v-model="form.one_tonnes" :error="form.errors.one_tonnes">
                <template #prefix-addon>
                  <div class="input-group-text">One tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
              <TextInput type="text" v-model="form.two_tonnes" :error="form.errors.two_tonnes">
                <template #prefix-addon>
                  <div class="input-group-text">Two tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
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
