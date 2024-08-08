<script setup>
import { watch } from "vue";
import Multiselect from '@vueform/multiselect';
import { useForm, usePage } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import { toTonnes, getSingleCategoryNameByType } from '@/helper.js';

const page = usePage();
const props = defineProps({
  allocations: {
    type: Array,
    default: [],
  },
});

const emit = defineEmits(['close']);

const form = useForm({
  allocations: props.allocations,
  buyer_id: null,
  half_tonnes: 0,
  one_tonnes: 0,
  two_tonnes: 0,
  weight: null,
});

const storeRecord = () => {
  form.post(route('allocation.duplicate'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      const modal = document.getElementById('relocate-modal');
      modal.querySelector('.btn-close').click();
      emit('close');
    },
  });
};

const onChangeBins = () => {
  form.half_tonnes  = 0;
  form.one_tonnes   = 0;
  form.two_tonnes   = 0;
  form.weight       = 0;
  
  form.allocations.forEach(allocation => {
    form.half_tonnes  += parseFloat(allocation.half_tonnes);
    form.one_tonnes   += parseFloat(allocation.one_tonnes);
    form.two_tonnes   += parseFloat(allocation.two_tonnes);
    form.weight       += parseFloat(allocation.weight);
  });
};

watch(
  () => props.allocations,
  (allocations) => {
    if (Object.values(page.props.errors).length > 0) {
      return;
    }
    allocations.forEach(allocation => {
      form.allocations[allocation.id] = {
        id: allocation.id,
        half_tonnes: 0,
        one_tonnes: 0,
        two_tonnes: 0,
        weight: 0,
      };
    });
  },
);
</script>

<template>
  <div class="modal fade" id="relocate-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 v-if="allocations.length > 0" class="modal-title" id="relocate-modal-label">
            {{ $page.props.buyers.find((buyer) => buyer.value === allocations[0].buyer_id).label }}
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="$emit('close')"
          ></button>
        </div>
        <div v-if="allocations.length > 0" class="modal-body">
          <template v-for="allocation in allocations" :key="allocation.id">
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
                  <td>{{ allocation.item.half_tonnes }} Bins</td>
                  <td>{{ allocation.item.one_tonnes }} Bins</td>
                  <td>{{ allocation.item.two_tonnes }} Bins</td>
                  <td>{{ toTonnes(allocation.item.weight) }}</td>
                </tr>
                </tbody>
              </table>
            </div>
            <div class="row my-3">
              <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
                <div class="input-group p-0" :class="{ 'is-invalid': form.errors[`allocations.${allocation.id}.half_tonnes`] }">
                  <div class="input-group-text">Half tonne</div>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.allocations[allocation.id].half_tonnes"
                    @input="onChangeBins"
                  />
                  <div class="input-group-text">Bins</div>
                </div>
                <div v-if="form.errors[`allocations.${allocation.id}.half_tonnes`]" class="invalid-feedback">
                  {{ form.errors[`allocations.${allocation.id}.half_tonnes`] }}
                </div>
              </div>
              <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
                <div class="input-group p-0" :class="{ 'is-invalid': form.errors[`allocations.${allocation.id}.one_tonnes`] }">
                  <div class="input-group-text">One tonne</div>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.allocations[allocation.id].one_tonnes"
                    @input="onChangeBins"
                  />
                  <div class="input-group-text">Bins</div>
                </div>
                <div v-if="form.errors[`allocations.${allocation.id}.one_tonnes`]" class="invalid-feedback">
                  {{ form.errors[`allocations.${allocation.id}.one_tonnes`] }}
                </div>
              </div>
              <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
                <div class="input-group p-0" :class="{ 'is-invalid': form.errors[`allocations.${allocation.id}.two_tonnes`] }">
                  <div class="input-group-text">Two tonne</div>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.allocations[allocation.id].two_tonnes"
                    @input="onChangeBins"
                  />
                  <div class="input-group-text">Bins</div>
                </div>
                <div v-if="form.errors[`allocations.${allocation.id}.two_tonnes`]" class="invalid-feedback">
                  {{ form.errors[`allocations.${allocation.id}.two_tonnes`] }}
                </div>
              </div>
              <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
                <div class="input-group p-0" :class="{ 'is-invalid': form.errors[`allocations.${allocation.id}.weight`] }">
                  <div class="input-group-text">Weight</div>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.allocations[allocation.id].weight"
                    @input="onChangeBins"
                  />
                  <div class="input-group-text">tonnes</div>
                </div>
                <div v-if="form.errors[`allocations.${allocation.id}.weight`]" class="invalid-feedback">
                  {{ form.errors[`allocations.${allocation.id}.weight`] }}
                </div>
              </div>
            </div>
          </template>
          <div class="mb-3">
            <label class="form-label">Buyer to allocate</label>
            <Multiselect
              v-model="form.buyer_id"
              mode="single"
              placeholder="Choose a buyer"
              :searchable="true"
              :options="$page.props.buyers"
              :class="{ 'is-invalid': form.errors.buyer_id }"
            />
            <div v-if="form.errors.buyer_id" class="invalid-feedback mt-2 p-0 m-0" v-text="form.errors.buyer_id" />
            <div :class="{ 'is-invalid': form.errors.allocations }"></div>
            <div v-if="form.errors.allocations" class="invalid-feedback mt-2 p-0 m-0" v-text="form.errors.allocations" />
          </div>
          <div class="row my-3">
            <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
              <TextInput type="text" v-model="form.half_tonnes" :error="form.errors.half_tonnes" :disabled="true">
                <template #prefix-addon>
                  <div class="input-group-text">Half tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
              <TextInput type="text" v-model="form.one_tonnes" :error="form.errors.one_tonnes" :disabled="true">
                <template #prefix-addon>
                  <div class="input-group-text">One tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
              <TextInput type="text" v-model="form.two_tonnes" :error="form.errors.two_tonnes" :disabled="true">
                <template #prefix-addon>
                  <div class="input-group-text">Two tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3 col-xl-3 mb-3">
              <TextInput type="text" v-model="form.weight" :error="form.errors.weight" :disabled="true">
                <template #prefix-addon>
                  <div class="input-group-text">Weight</div>
                </template>
                <template #addon>
                  <div class="input-group-text">tonnes</div>
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
