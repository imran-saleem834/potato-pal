<script setup>
import moment from 'moment';
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect';
import { useToast } from "vue-toastification";
import TextInput from "@/Components/TextInput.vue";
import Images from "@/Components/Images.vue";
import {
  toCamelCase,
  getDropDownOptions,
  getCategoriesDropDownByType,
  getCategoryIdsByType,
  getCategoriesByType
} from "@/helper.js";

const toast = useToast();

const userGrowerGroups = ref([]);
const paddockOptions = ref([]);

const props = defineProps({
  receival: Object,
  colSize: String,
  isEdit: Boolean,
  isNew: Boolean,
  users: Array,
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
  comments: props.receival.comments,
});

const statusForm = useForm({});

watch(() => props.receival,
  (receival) => {
    form.clearErrors();
    form.grower_id = receival.grower_id
    form.grower_group = getCategoryIdsByType(receival.categories, 'grower-group')
    form.paddocks = receival.paddocks
    form.seed_variety = getCategoryIdsByType(receival.categories, 'seed-variety')
    form.seed_generation = getCategoryIdsByType(receival.categories, 'seed-generation')
    form.seed_class = getCategoryIdsByType(receival.categories, 'seed-class')
    form.delivery_type = getCategoryIdsByType(receival.categories, 'delivery-type')
    form.transport = getCategoryIdsByType(receival.categories, 'transport')
    form.grower_docket_no = receival.grower_docket_no
    form.chc_receival_docket_no = receival.chc_receival_docket_no
    form.driver_name = receival.driver_name
    form.comments = receival.comments

    updatePaddock(receival.grower_id);
    resetGrowerGroups(receival.grower_id);
  }
);

watch(() => form.grower_id,
  (growerId) => {
    updatePaddock(growerId);
    resetGrowerGroups(growerId);
  }
);

const onChangeGrowerUser = (grower_id) => {
  resetGrowerGroups(grower_id);
  form.grower = [];
}

const resetGrowerGroups = (grower_id) => {
  const categories = props.users
    ?.find(user => user.id === grower_id)
    ?.categories;

  userGrowerGroups.value = getCategoriesByType(categories, 'grower-group')?.map(item => {
    return { 'value': item.category.id, 'label': item.category.name };
  });
}

const updatePaddock = (growerId) => {
  if (growerId) {
    const user = props.users.find(user => user.id === growerId);
    paddockOptions.value = (user.paddocks || []).map(paddock => paddock.name);
    paddockOptions.value = [...paddockOptions.value, ...props.receival?.paddocks || []];
    return;
  }
  paddockOptions.value = [];
}

updatePaddock(props.receival.grower_id);
resetGrowerGroups(props.receival.grower_id);

const isForm = computed(() => props.isEdit || props.isNew)
const isRequireFieldsEmpty = computed(() => {
  const growerGroup = getCategoryIdsByType(props.receival.categories, 'grower-group');
  if (growerGroup.length <= 0) {
    return true;
  }
  const seedVariety = getCategoryIdsByType(props.receival.categories, 'seed-variety');
  if (seedVariety.length <= 0) {
    return true;
  }
  const seedClass = getCategoryIdsByType(props.receival.categories, 'seed-class');
  if (seedClass.length <= 0) {
    return true;
  }
  const seedGeneration = getCategoryIdsByType(props.receival.categories, 'seed-generation');
  if (seedGeneration.length <= 0) {
    return true;
  }
  return props.receival.paddocks?.length <= 0;
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
}

const storeRecord = () => {
  form.post(route('receivals.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The receival has been created successfully!');
    },
  });
}

defineExpose({
  updateRecord,
  storeRecord
});

const pushForTiaSample = () => {
  statusForm.post(route('receivals.push.tia-sample', props.receival.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('updateRecord');
      toast.success('The receival pushed for tia sample successfully!');
    },
  });
}

const pushForUnload = () => {
  statusForm.post(route('receivals.push.unload', props.receival.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('updateRecord');
      toast.success('The receival pushed for unloading successfully!');
    },
  });
}
</script>

<template>
  <div class="row">
    <div v-if="isRequireFieldsEmpty" class="col-12">
      <div class="alert alert-warning" role="alert">
        Require these fields (Grower Group, Paddocks, Seed Variety, Class and Generation) to list in the allocations
      </div>
    </div>
    <div :class="colSize">
      <h4>Grower Information</h4>
      <div class="user-boxes">
        <table class="table mb-0">
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
                :class="{'is-invalid' : form.errors.grower_id}"
                :options="getDropDownOptions(users, true)"
              />
              <Link
                v-else-if="receival.grower"
                class="p-0"
                :href="route('users.index', { userId: receival.grower_id })"
              >
                {{ receival.grower?.name }}
                {{ receival.grower?.grower_name ? ' (' + receival.grower?.grower_name + ')' : '' }}
              </Link>
              <template v-else>-</template>
              <div v-if="form.errors.grower_id" class="invalid-feedback">{{ form.errors.grower_id }}</div>
            </td>
          </tr>
          <tr>
            <th>Grower Group</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.grower_group"
                mode="tags"
                placeholder="Choose a grower group"
                :searchable="true"
                :create-option="true"
                :options="userGrowerGroups"
                :class="{'is-invalid' : form.errors.grower_group}"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(receival.categories, 'grower-group').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'grower-group')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.grower_group" class="invalid-feedback" v-text="form.errors.grower_group"/>
            </td>
          </tr>
          <tr v-if="!isForm">
            <th>Time Added</th>
            <td>{{ moment(receival.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
          </tr>
          <tr>
            <th>Paddock</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.paddocks"
                mode="tags"
                placeholder="Choose a paddock"
                :searchable="true"
                :create-option="true"
                :options="paddockOptions"
                :class="{'is-invalid' : form.errors.paddocks}"
              />
              <ul class="p-0" v-else-if="receival.paddocks?.length">
                <li v-for="paddock in receival.paddocks" :key="paddock">
                  <a>{{ paddock }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.paddocks" class="invalid-feedback" v-text="form.errors.paddocks"/>
            </td>
          </tr>
        </table>
      </div>

      <h4>Seed Information</h4>
      <div class="user-boxes">
        <table class="table mb-0">
          <tr>
            <th>Seed Variety</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.seed_variety"
                mode="tags"
                placeholder="Choose a seed variety"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'seed-variety')"
                :class="{'is-invalid' : form.errors.seed_variety}"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(receival.categories, 'seed-variety').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'seed-variety')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.seed_variety" class="invalid-feedback" v-text="form.errors.seed_variety"/>
            </td>
          </tr>
          <tr>
            <th>Seed Generation</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.seed_generation"
                mode="tags"
                placeholder="Choose a seed generation"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'seed-generation')"
                :class="{'is-invalid' : form.errors.seed_generation}"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(receival.categories, 'seed-generation').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'seed-generation')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.seed_generation" class="invalid-feedback" v-text="form.errors.seed_generation"/>
            </td>
          </tr>
          <tr>
            <th>Seed Class</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.seed_class"
                mode="tags"
                placeholder="Choose a seed class"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'seed-class')"
                :class="{'is-invalid' : form.errors.seed_class}"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(receival.categories, 'seed-class').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'seed-class')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.seed_class" class="invalid-feedback" v-text="form.errors.seed_class"/>
            </td>
          </tr>
          <tr v-if="!isForm">
            <th>Tia Sample Status</th>
            <td>
              <ul class="p-0">
                <li>
                  <button
                    v-if="receival.tia_sample"
                    :class="receival.tia_sample?.status === 'pending' ? 'btn btn-pending' : 'btn btn-dark'"
                  >{{ toCamelCase(receival.tia_sample?.status) }}
                  </button>
                  <button
                    v-else
                    class="btn btn-black"
                    @click="pushForTiaSample"
                    :disabled="statusForm.processing"
                  >
                    <template v-if="statusForm.processing">
                      <i class="bi bi-arrow-repeat d-inline-block spin"></i> Loading...
                    </template>
                    <template v-else>Push for TIA Sample</template>
                  </button>
                </li>
              </ul>
            </td>
          </tr>
          <tr v-if="!isForm">
            <th>TIA Sample ID</th>
            <td>
              <Link
                v-if="receival.tia_sample"
                class="p-0"
                :href="route('tia-samples.index', { tiaSampleId: receival.tia_sample.id })"
              >
                {{ receival.tia_sample.id }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="!isNew">Docket Receipts</h4>
      <div v-if="!isNew" class="user-boxes notes-list">
        <Images
          :images="receival.images"
          :upload-route="route('receivals.upload', receival.id || 0)"
          :delete-route="route('receivals.delete', receival.id || 0)"
          @updateRecord="emit('update')"
        />
      </div>
    </div>
    <div :class="colSize">
      <h4 v-if="!isForm">Unloading Information</h4>
      <div v-if="!isForm" class="user-boxes">
        <table class="table mb-0">
          <tr>
            <th>Cool Store</th>
            <td class="pb-0">
              <ul class="p-0" v-if="getCategoriesByType(receival.grower.categories, 'cool-store').length">
                <li v-for="category in getCategoriesByType(receival.grower.categories, 'cool-store')"
                    :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Unloading Status</th>
            <td>
              <ul class="p-0">
                <li>
                  <button
                    v-if="receival.status"
                    :class="receival.status === 'pending' ? 'btn btn-pending' : 'btn btn-dark'"
                  >{{ toCamelCase(receival.status) }}
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
                :href="route('unloading.index', { unloadId: receival.id })"
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
        <table class="table mb-0">
          <tr>
            <th>Transport Co.</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.transport"
                mode="tags"
                placeholder="Choose a transport"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'transport')"
                :class="{'is-invalid' : form.errors.transport}"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(receival.categories, 'transport').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'transport')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.transport" class="invalid-feedback" v-text="form.errors.transport"/>
            </td>
          </tr>
          <tr>
            <th>Delivery Type</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.delivery_type"
                mode="tags"
                placeholder="Choose a delivery type"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'delivery-type')"
                :class="{'is-invalid' : form.errors.delivery_type}"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(receival.categories, 'delivery-type').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'delivery-type')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.delivery_type" class="invalid-feedback" v-text="form.errors.delivery_type"/>
            </td>
          </tr>
          <tr>
            <th>Growers’s Docket No</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.grower_docket_no"
                :error="form.errors.grower_docket_no"
                type="text"
              />
              <template v-else-if="receival.grower_docket_no">{{ receival.grower_docket_no }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>CHC Receival Docket No</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.chc_receival_docket_no"
                :error="form.errors.chc_receival_docket_no"
                type="text"
              />
              <template v-else-if="receival.chc_receival_docket_no">{{ receival.chc_receival_docket_no }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Driver’s Name</th>
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
