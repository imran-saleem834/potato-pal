<script setup>
import moment from 'moment';
import { computed, ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import TextInput from '@/Components/TextInput.vue';
import Images from '@/Components/Images.vue';
import {
  toCamelCase,
  getDropDownOptions,
  getCategoriesDropDownByType,
  getCategoryIdsByType,
  getCategoriesByType,
} from '@/helper.js';
import ItemOfCategories from '@/Components/ItemOfCategories.vue';
import CustomDatePicker from '@/Components/CustomDatePicker.vue';

const toast = useToast();

const userGrowerGroups = ref([]);
const paddockOptions = ref([]);

const props = defineProps({
  receival: Object,
  colSize: String,
  isEdit: Boolean,
  isNew: Boolean,
  users: Array,
  buyers: Array,
  categories: Array,
});

const emit = defineEmits(['update', 'create', 'unset']);

const form = useForm({
  grower_id: props.receival.grower_id,
  grower_group: getCategoryIdsByType(props.receival.categories, 'grower-group'),
  paddocks: props.receival.paddocks,
  seed_variety: getCategoryIdsByType(props.receival.categories, 'seed-variety'),
  seed_generation: getCategoryIdsByType(props.receival.categories, 'seed-generation'),
  seed_class: getCategoryIdsByType(props.receival.categories, 'seed-class'),
  delivery_type: getCategoryIdsByType(props.receival.categories, 'delivery-type'),
  transport: getCategoryIdsByType(props.receival.categories, 'transport'),
  grower_docket_no: props.receival.grower_docket_no,
  chc_receival_docket_no: props.receival.chc_receival_docket_no,
  driver_name: props.receival.driver_name,
  dummy_buyer_id: props.receival.dummy_buyer_id,
  comments: props.receival.comments,
  created_at: props.receival.created_at
    ? props.receival.created_at.split('.')[0].replace('T', ' ')
    : null,
});

const statusForm = useForm({});

watch(
  () => props.receival,
  (receival) => {
    form.clearErrors();
    form.grower_id = receival.grower_id;
    form.grower_group = getCategoryIdsByType(receival.categories, 'grower-group');
    form.paddocks = receival.paddocks;
    form.seed_variety = getCategoryIdsByType(receival.categories, 'seed-variety');
    form.seed_generation = getCategoryIdsByType(receival.categories, 'seed-generation');
    form.seed_class = getCategoryIdsByType(receival.categories, 'seed-class');
    form.delivery_type = getCategoryIdsByType(receival.categories, 'delivery-type');
    form.transport = getCategoryIdsByType(receival.categories, 'transport');
    form.grower_docket_no = receival.grower_docket_no;
    form.chc_receival_docket_no = receival.chc_receival_docket_no;
    form.driver_name = receival.driver_name;
    form.dummy_buyer_id = receival.dummy_buyer_id;
    form.comments = receival.comments;
    form.created_at = receival.created_at
      ? receival.created_at.split('.')[0].replace('T', ' ')
      : null;

    updatePaddock(receival.grower_id);
    resetGrowerGroups(receival.grower_id);
  },
);

watch(
  () => form.grower_id,
  (growerId) => {
    updatePaddock(growerId);
    resetGrowerGroups(growerId);
  },
);

const onChangeGrowerUser = (grower_id) => {
  resetGrowerGroups(grower_id);
  form.grower = [];
};

const resetGrowerGroups = (grower_id) => {
  const categories = props.users?.find((user) => user.id === grower_id)?.categories;

  userGrowerGroups.value = getCategoriesByType(categories, 'grower-group')?.map((item) => {
    return { value: item.category.id, label: item.category.name };
  });
};

const updatePaddock = (growerId) => {
  if (growerId) {
    const user = props.users.find((user) => user.id === growerId);
    paddockOptions.value = (user.paddocks || []).map((paddock) => paddock.name);
    paddockOptions.value = [...paddockOptions.value, ...(props.receival?.paddocks || [])];
    return;
  }
  paddockOptions.value = [];
};

updatePaddock(props.receival.grower_id);
resetGrowerGroups(props.receival.grower_id);

const isForm = computed(() => props.isEdit || props.isNew);
const requireFields = computed(() => {
  const fields = [];
  const growerGroup = getCategoryIdsByType(props.receival.categories, 'grower-group');
  if (growerGroup.length <= 0) {
    fields.push('Grower Group');
  }
  const seedVariety = getCategoryIdsByType(props.receival.categories, 'seed-variety');
  if (seedVariety.length <= 0) {
    fields.push('Seed Variety');
  }
  const seedClass = getCategoryIdsByType(props.receival.categories, 'seed-class');
  if (seedClass.length <= 0) {
    fields.push('Seed Class');
  }
  const seedGeneration = getCategoryIdsByType(props.receival.categories, 'seed-generation');
  if (seedGeneration.length <= 0) {
    fields.push('Seed Generation');
  }
  if (props.receival.paddocks?.length <= 0) {
    fields.push('Paddocks');
  }
  if (props.receival.status !== 'completed') {
    fields.push('Unloading should be completed');
  }
  return fields;
});

const updateRecord = () => {
  form.patch(route('receivals.update', props.receival.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The receival has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('receivals.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The receival has been created successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});

const pushForAnotherInspection = (status) => {
  statusForm.post(route('receivals.push.tia-sample', { id: props.receival.id, status }), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The receival push for tia inspection successfully!');
    },
  });
};

const pushForUnload = () => {
  statusForm.post(route('receivals.push.unload', props.receival.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The receival pushed for unloading successfully!');
    },
  });
};
</script>

<template>
  <div class="row">
    <div v-if="!isForm && requireFields.length > 0" class="col-12">
      <div class="alert alert-warning" role="alert">
        Complete the following tasks to be eligible for selection in the allocation process.
        <ul class="mb-0">
          <li v-for="field in requireFields" :key="field">
            {{ field }}
          </li>
        </ul>
      </div>
    </div>
    <div :class="colSize">
      <h4>Grower Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Grower</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.grower_id"
                mode="single"
                placeholder="Choose a grower"
                :searchable="true"
                @change="onChangeGrowerUser"
                :class="{ 'is-invalid': form.errors.grower_id }"
                :options="getDropDownOptions(users, true)"
              />
              <Link
                v-else-if="receival.grower"
                class="p-0"
                :href="route('users.index', { userId: receival.grower_id })"
              >
                {{ receival.grower?.grower_name }}
              </Link>
              <template v-else>-</template>
              <div v-if="form.errors.grower_id" class="invalid-feedback">
                {{ form.errors.grower_id }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Grower Group</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.grower_group"
                mode="tags"
                placeholder="Choose a grower group"
                :searchable="true"
                :create-option="true"
                :options="userGrowerGroups"
                :class="{ 'is-invalid': form.errors.grower_group }"
              />
              <ItemOfCategories v-else :categories="receival.categories" type="grower-group" />
              <div
                v-if="form.errors.grower_group"
                class="invalid-feedback"
                v-text="form.errors.grower_group"
              />
            </td>
          </tr>
          <tr>
            <th>Receival Time</th>
            <td>
              <CustomDatePicker v-if="isForm" :form="form" field="created_at" />
              <template v-else>
                {{ moment(receival.created_at).format('DD/MM/YYYY hh:mm A') }}
              </template>
            </td>
          </tr>
          <tr>
            <th>Paddock</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.paddocks"
                mode="tags"
                placeholder="Choose a paddock"
                :searchable="true"
                :create-option="true"
                :options="paddockOptions"
                :class="{ 'is-invalid': form.errors.paddocks }"
              />
              <ul class="p-0" v-else-if="receival.paddocks?.length">
                <li v-for="paddock in receival.paddocks" :key="paddock">
                  <a>{{ paddock }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div
                v-if="form.errors.paddocks"
                class="invalid-feedback"
                v-text="form.errors.paddocks"
              />
            </td>
          </tr>
        </table>
      </div>

      <h4>Seed Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Seed Variety</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.seed_variety"
                mode="tags"
                placeholder="Choose a seed variety"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'seed-variety')"
                :class="{ 'is-invalid': form.errors.seed_variety }"
              />
              <ItemOfCategories v-else :categories="receival.categories" type="seed-variety" />
              <div
                v-if="form.errors.seed_variety"
                class="invalid-feedback"
                v-text="form.errors.seed_variety"
              />
            </td>
          </tr>
          <tr>
            <th>Seed Generation</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.seed_generation"
                mode="tags"
                placeholder="Choose a seed generation"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'seed-generation')"
                :class="{ 'is-invalid': form.errors.seed_generation }"
              />
              <ItemOfCategories v-else :categories="receival.categories" type="seed-generation" />
              <div
                v-if="form.errors.seed_generation"
                class="invalid-feedback"
                v-text="form.errors.seed_generation"
              />
            </td>
          </tr>
          <tr>
            <th>Seed Class</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.seed_class"
                mode="tags"
                placeholder="Choose a seed class"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'seed-class')"
                :class="{ 'is-invalid': form.errors.seed_class }"
              />
              <ItemOfCategories v-else :categories="receival.categories" type="seed-class" />
              <div
                v-if="form.errors.seed_class"
                class="invalid-feedback"
                v-text="form.errors.seed_class"
              />
            </td>
          </tr>
          <tr v-if="!isForm && receival.tia_samples.length > 0">
            <th>Inspection ID</th>
            <td>
              <Link
                v-for="tia_sample in receival.tia_samples"
                :key="tia_sample.id"
                class="p-0 me-2"
                :href="route('tia-samples.index', { tiaSampleId: tia_sample.id })"
              >
                {{ tia_sample.id }}
              </Link>
              <button
                @click="pushForAnotherInspection"
                class="border-0 ms-1 text-decoration-underline"
              >
                Push for another inspection
              </button>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="!isNew">Docket Receipts</h4>
      <div v-if="!isNew" class="user-boxes notes-list">
        <Images
          type="receivals"
          :id="receival.id || 0"
          :images="receival.images || []"
          @update="emit('update')"
        />
      </div>
    </div>
    <div :class="colSize">
      <h4 v-if="!isForm">Unloading Information</h4>
      <div v-if="!isForm" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Unloading Status</th>
            <td class="pb-0">
              <ul class="p-0">
                <li>
                  <button
                    v-if="receival.status"
                    :class="receival.status === 'pending' ? 'btn btn-pending' : 'btn btn-dark'"
                  >
                    {{ toCamelCase(receival.status) }}
                  </button>
                  <button
                    v-else
                    class="btn btn-black"
                    @click="pushForUnload"
                    :disabled="statusForm.processing"
                  >
                    <template v-if="statusForm.processing">
                      <i class="bi bi-arrow-repeat d-inline-block spin"></i> Loading...
                    </template>
                    <template v-else> Push for Unload</template>
                  </button>
                </li>
              </ul>
            </td>
          </tr>
          <tr>
            <th>Unloading ID</th>
            <td>
              <Link
                v-if="receival.status"
                class="p-0"
                :href="route('unloading.index', { receivalId: receival.id })"
              >
                {{ receival.id }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>

      <h4>Other Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Grower's Docket No.</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.grower_docket_no"
                :error="form.errors.grower_docket_no"
                type="text"
              />
              <template v-else-if="receival.grower_docket_no">
                {{ receival.grower_docket_no }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>CHC Receival Docket No.</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.chc_receival_docket_no"
                :error="form.errors.chc_receival_docket_no"
                type="text"
              />
              <template v-else-if="receival.chc_receival_docket_no">
                {{ receival.chc_receival_docket_no }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Delivery Type</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.delivery_type"
                mode="tags"
                placeholder="Choose a delivery type"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'delivery-type')"
                :class="{ 'is-invalid': form.errors.delivery_type }"
              />
              <ItemOfCategories v-else :categories="receival.categories" type="delivery-type" />
              <div
                v-if="form.errors.delivery_type"
                class="invalid-feedback"
                v-text="form.errors.delivery_type"
              />
            </td>
          </tr>
          <tr>
            <th>Transport Co.</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.transport"
                mode="tags"
                placeholder="Choose a transport"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'transport')"
                :class="{ 'is-invalid': form.errors.transport }"
              />
              <ItemOfCategories v-else :categories="receival.categories" type="transport" />
              <div
                v-if="form.errors.transport"
                class="invalid-feedback"
                v-text="form.errors.transport"
              />
            </td>
          </tr>
          <tr>
            <th>Driver</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.driver_name"
                :error="form.errors.driver_name"
                type="text"
              />
              <template v-else-if="receival.driver_name">{{ receival.driver_name }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Buyer</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.dummy_buyer_id"
                mode="single"
                placeholder="Choose a buyer"
                :searchable="true"
                :options="buyers"
                :class="{ 'is-invalid': form.errors.dummy_buyer_id }"
              />
              <template v-else-if="receival.dummy_buyer">
                {{ receival.dummy_buyer?.buyer_name }}
              </template>
              <template v-else>-</template>
              <div
                v-if="form.errors.dummy_buyer_id"
                class="invalid-feedback"
                v-text="form.errors.dummy_buyer_id"
              />
            </td>
          </tr>
          <tr>
            <th>Comments</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.comments"
                :error="form.errors.comments"
                type="text"
              />
              <template v-else-if="receival.comments">{{ receival.comments }}</template>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>
