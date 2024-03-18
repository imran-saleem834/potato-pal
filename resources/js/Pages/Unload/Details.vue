<script setup>
import moment from 'moment';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import UlLiButton from '@/Components/UlLiButton.vue';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import {
  toTonnes,
  getSingleCategoryNameByType,
  getCategoriesDropDownByType,
  getCategoriesByType,
  getCategoryIdsByType,
} from '@/helper.js';
import { binSizes, tiaStatus, tiaStatusInit } from '@/const.js';
import ItemOfCategories from "@/Components/ItemOfCategories.vue";
import TdOfCategories from "@/Components/TdOfCategories.vue";

const toast = useToast();

const props = defineProps({
  receival: Object,
  categories: Array,
  colSize: String,
  isEdit: Boolean,
});

const bins = ref([]);
const weight = ref([]);
const weightError = ref([]);
const getWeightIndex = ref(null);

const emit = defineEmits(['update', 'create']);

const form = useForm({
  fungicide: getCategoryIdsByType(props.receival.categories, 'fungicide'),
  status: props.receival.status,
  unloads: props.receival.unloads,
});

watch(
  () => props.receival,
  (receival) => {
    form.clearErrors();
    form.status = receival.status;

    updateUnloadsOnChangeReceival(receival);
  },
);

const getDefaultSeedType = () => {
  const category = getCategoriesByType(props.categories, 'seed-type').find((category) =>
    category.name.includes('Standard'),
  );

  return [{ category_id: category.id, type: 'seed-type', category }];
};

const defaultUnloadFields = {
  categories: getDefaultSeedType(),
  seed_type: null,
  type: null,
  bin_size: null,
  no_of_bins: 0,
  weight: 0,
  channel: null,
  system: null,
};

const updateUnloadForm = () => {
  form.unloads.forEach((unload, index) => {
    form.unloads[index].seed_type = getCategoryIdsByType(unload.categories, 'seed-type')[0];
    form.unloads[index].created_at = unload.created_at
      ? moment(unload.created_at).utc().format('YYYY-MM-DDThh:mm')
      : null;

    bins.value[index] = null;
    weight.value[index] = null;
    weightError.value[index] = null;
  });
};

const updateUnloadsOnChangeReceival = (receival) => {
  form.fungicide = getCategoryIdsByType(receival.categories, 'fungicide');
  form.unloads = receival.unloads.length <= 0 ? [{ ...defaultUnloadFields }] : [...receival.unloads];

  updateUnloadForm();
};

const resetBinsAndWeight = (index) => {
  form.unloads[index].no_of_bins = null;
  form.unloads[index].weight = null;
};

const onChangeSeedType = (value, index) => {
  form.unloads[index].seed_type = value;

  resetBinsAndWeight(index);
};

const onChangeWeightType = (value, index) => {
  form.unloads[index].type = value;

  resetBinsAndWeight(index);
}

const onChangeChannel = (value, index) => {
  form.unloads[index].channel = value;
  form.unloads[index].bin_size = value === 'weighbridge' ? 1000 : 2000;

  resetBinsAndWeight(index);
};

const onChangeBinSize = (value, index) => {
  form.unloads[index].bin_size = value;

  resetBinsAndWeight(index);
};

const onChangeSystem = (value, index) => {
  form.unloads[index].system = value;

  resetBinsAndWeight(index);
};

updateUnloadsOnChangeReceival(props.receival);

const addMoreUnload = () => {
  form.unloads.push({ ...defaultUnloadFields });

  updateUnloadForm();
};

const page = usePage();
const getWeightMessage = reactive({
  seed: '',
  bins: '',
  responseChannel: null,
  staffID: page.props.auth.user.id,
  system: null,
  terminalCommand: 'S',
});

const startWeighingMessage = reactive({
  responseChannel: null,
  startWeighing: 'PotatoPal',
  exWhomName: props.receival.grower.grower_name || '',
  exWhomGroupName: getSingleCategoryNameByType(props.receival.categories, 'group'),
  potIdentifierCode: props.receival.id,
  taskOwner: page.props.auth.user.name,
  emptyBinWeight: props.receival.bin_size === 1 ? '120' : '204',
  binSize: props.receival.bin_size,
  numberOfBinsToWeigh: null,
  seedType: getSingleCategoryNameByType(props.receival.categories, 'seed-type'),
  taskStartDate: null,
  taskStatus: 'Pending',
  fungicide: getSingleCategoryNameByType(props.receival.categories, 'fungicide'),
  receivalID: props.receival.id,
  staffID: page.props.auth.user.id,
});

const getWeight = (index) => {
  getWeightIndex.value = index;
  weightError.value[index] = null;
  if (!form.unloads[index].channel) {
    weightError.value[index] = 'Channel not selected.';
    return;
  }
  if (!form.unloads[index].system) {
    weightError.value[index] = 'System not selected.';
    return;
  }
  if (!bins.value[index]) {
    weightError.value[index] = 'Bins not selected.';
    return;
  }
  getWeightMessage.responseChannel = window.pubnub.deviceId;
  getWeightMessage.system = form.unloads[index].system;
  const publishPayload = {
    channel: form.unloads[index].channel,
    message: getWeightMessage,
  };
  window.pubnub.instance.publish(publishPayload);
};

const startWeighing = (index) => {
  startWeighingMessage.responseChannel = window.pubnub.deviceId;
  startWeighingMessage.taskStartDate = new Date();
  startWeighingMessage.numberOfBinsToWeigh = bins.value[index];
  const publishPayload = {
    channel: form.unloads[index].channel,
    message: startWeighingMessage,
  };
  window.pubnub.instance.publish(publishPayload);
};

onMounted(() => {
  document.addEventListener('GetWeight', (event) => {
    console.log('WeightReceived', event?.detail);
    weight.value[getWeightIndex.value] = event?.detail?.CurrentWeight?.toFixed(2);
    getWeightIndex.value = null;
  });
});

const acceptWeight = (index) => {
  form.unloads[index].no_of_bins =
    parseInt(form.unloads[index].no_of_bins ?? 0) + parseInt(bins.value[index]);
  form.unloads[index].weight =
    parseFloat(form.unloads[index].weight ?? 0) + parseFloat(weight.value[index]);

  rejectWeight(index);
};

const rejectWeight = (index) => {
  bins.value[index] = null;
  weight.value[index] = null;
};

const isForm = computed(() => props.isEdit);

const updateRecord = () => {
  form.patch(route('unloading.update', props.receival.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The unload has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('unloading.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The unload has been created successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});
</script>

<template>
  <div class="row">
    <div :class="colSize">
      <h4>Seed Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Grower</th>
            <td>
              <Link
                v-if="receival.grower"
                class="p-0"
                :href="route('users.index', { userId: receival.grower.id })"
              >
                {{ receival.grower?.grower_name }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Receival id</th>
            <td>
              <Link class="p-0" :href="route('receivals.index', { receivalId: receival.id })">
                {{ receival.id }}
              </Link>
            </td>
          </tr>
          <tr>
            <th>Receival time</th>
            <td>{{ moment(receival.created_at).utc().format('DD/MM/YYYY hh:mm A') }}</td>
          </tr>
          <tr>
            <th>Grower Group</th>
            <TdOfCategories :categories="receival.categories" type="grower-group"/>
          </tr>
          <tr>
            <th>Paddock</th>
            <td class="pb-0">
              <ul class="p-0" v-if="receival.paddocks">
                <li v-for="paddock in receival.paddocks" :key="paddock">
                  <a>{{ paddock }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Variety</th>
            <TdOfCategories :categories="receival.categories" type="seed-variety"/>
          </tr>
          <tr>
            <th>Gen</th>
            <TdOfCategories :categories="receival.categories" type="seed-generation"/>
          </tr>
          <tr>
            <th>TIA sampling</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="false"
                :value="receival.tia_status"
                :items="
                  receival.tia_status && !receival.tia_status.includes('applied')
                    ? tiaStatus
                    : tiaStatusInit
                "
              />
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div :class="colSize">
      <h4>Unloading Information to Record</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Status</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.status"
                :error="form.errors.status"
                :items="[
                  { value: 'pending', label: 'Pending' },
                  { value: 'completed', label: 'Completed' },
                ]"
                @click="(value) => (form.status = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Fungicide</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.fungicide"
                mode="tags"
                placeholder="Choose a fungicide"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'fungicide')"
                :class="{ 'is-invalid': form.errors.fungicide }"
              />
              <ItemOfCategories v-else :categories="receival.categories" type="fungicide"/>
              <div
                v-if="form.errors.fungicide"
                class="invalid-feedback"
                v-text="form.errors.fungicide"
              />
            </td>
          </tr>
        </table>
      </div>
      <div v-for="(unload, index) in form.unloads" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Unload time</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.unloads[index].created_at"
                :error="form.errors[`unloads.${index}.created_at`]"
                type="datetime-local"
              />
              <template v-else-if="unload.created_at">
                {{ moment(unload.created_at).format('DD/MM/YYYY hh:mm A') }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Seed type</th>
            <td :class="{'pb-0' : !isForm && getCategoriesByType(unload.categories, 'seed-type').length }">
              <Multiselect
                v-if="isForm && form.unloads[index]"
                v-model="form.unloads[index].seed_type"
                mode="single"
                placeholder="Choose a seed type"
                :searchable="true"
                @change="(value) => onChangeSeedType(value, index)"
                :class="{ 'is-invalid': form.errors[`unloads.${index}.seed_type`] }"
                :options="getCategoriesDropDownByType(categories, 'seed-type')"
              />
              <ItemOfCategories v-else :categories="unload.categories" type="seed-type"/>
              <div v-if="form.errors[`unloads.${index}.seed_type`]" class="invalid-feedback">
                {{ form.errors[`unloads.${index}.seed_type`] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Weight Type</th>
            <td class="pb-0">
              <UlLiButton
                v-if="form.unloads[index]"
                :is-form="isForm"
                :value="form.unloads[index].type"
                :error="form.errors[`unloads.${index}.type`]"
                :items="[
                  { value: 1, label: 'System' },
                  { value: 2, label: 'Manual' },
                ]"
                @click="(value) => onChangeWeightType(value, index)"
              />
            </td>
          </tr>
          <tr v-if="form.unloads[index]?.type === 1">
            <th>Channel</th>
            <td class="pb-0">
              <UlLiButton
                v-if="form.unloads[index]"
                :is-form="isForm"
                :value="form.unloads[index].channel"
                :error="form.errors[`unloads.${index}.channel`]"
                :items="[
                  { value: 'weighbridge', label: 'BU1' },
                  { value: 'BU2', label: 'BU2' },
                  { value: 'BU3', label: 'BU3' },
                ]"
                @click="(value) => onChangeChannel(value, index)"
              />
            </td>
          </tr>
          <tr>
            <th>Bin size</th>
            <td class="pb-0">
              <UlLiButton
                v-if="form.unloads[index]"
                :is-form="isForm"
                :value="form.unloads[index].bin_size"
                :error="form.errors[`unloads.${index}.bin_size`]"
                :items="binSizes"
                @click="(value) => onChangeBinSize(value, index)"
              />
            </td>
          </tr>
          <template v-if="isForm && form.unloads[index] && form.unloads[index]?.type === 1">
            <tr>
              <th>System</th>
              <td class="pb-0">
                <UlLiButton
                  :is-form="true"
                  :value="form.unloads[index].system"
                  :error="form.errors[`unloads.${index}.system`]"
                  :items="
                  form.unloads[index].channel === 'weighbridge'
                    ? [{ value: 1, label: 'System 1' }]
                    : [
                        { value: 1, label: 'System 1' },
                        { value: 2, label: 'System 2' },
                      ]
                "
                  @click="(value) => onChangeSystem(value, index)"
                />
              </td>
            </tr>
            <tr>
              <th>No. of total bins</th>
              <td>{{ form.unloads[index].no_of_bins || '-' }}</td>
            </tr>
            <tr>
              <th>Weight of total bins</th>
              <td>{{ toTonnes(form.unloads[index].weight) }}</td>
            </tr>
          </template>
          <template v-if="isForm && form.unloads[index] && form.unloads[index]?.type === 2">
            <tr>
              <th>No. of total bins</th>
              <td>
                <TextInput
                  v-model="form.unloads[index].no_of_bins"
                  :error="form.errors[`unloads.${index}.no_of_bins`]"
                  :disabled="form.unloads[index]?.type === 1"
                  type="text"
                />
              </td>
            </tr>
            <tr>
              <th>Weight of total bins</th>
              <td>
                <TextInput
                  v-model="form.unloads[index].weight"
                  :error="form.errors[`unloads.${index}.weight`]"
                  type="text"
                >
                  <template #addon>
                    <div class="input-group-text">kg</div>
                  </template>
                </TextInput>
              </td>
            </tr>
          </template>
          <template v-if="!isForm">
            <tr>
              <th>No. of total bins</th>
              <td>
                <template v-if="unload.no_of_bins">{{ unload.no_of_bins }}</template>
                <template v-else>-</template>
              </td>
            </tr>
            <tr>
              <th>Weight of total bins</th>
              <td>
                <template v-if="unload.weight">{{ toTonnes(unload.weight) }}</template>
                <template v-else>-</template>
              </td>
            </tr>
          </template>
          <tr v-if="isForm && form.unloads[index] && form.unloads[index]?.type === 1">
            <th>No. of bins weighed at a time</th>
            <td class="pb-0">
              <div class="d-inline-block">
                <UlLiButton
                  :is-form="true"
                  :value="bins[index]"
                  :items="[
                    { value: 1, label: '1' },
                    { value: 2, label: '2' },
                  ]"
                  :error="weightError[index]"
                  @click="(value) => (bins[index] = value)"
                />
              </div>
              <div class="d-inline-block align-top">
                <button @click="startWeighing(index)" class="btn btn-red me-2">Start Weight</button>
                <button @click="getWeight(index)" class="btn btn-red">Get Weight</button>
              </div>
            </td>
          </tr>
          <tr v-if="isForm && form.unloads[index] && form.unloads[index]?.type === 1">
            <th>Weight of bins</th>
            <td class="pb-0">
              <TextInput v-model="weight[index]" type="text">
                <template #addon>
                  <div class="input-group-text">kg</div>
                  <div
                    v-if="weight[index] && bins[index]"
                    class="input-group-text cursor-pointer"
                    @click="acceptWeight(index)"
                  >
                    <i class="bi bi-check-lg"></i>
                  </div>
                  <div
                    v-if="weight[index] && bins[index]"
                    class="input-group-text cursor-pointer"
                    @click="rejectWeight(index)"
                  >
                    <i class="bi bi-x-lg"></i>
                  </div>
                </template>
              </TextInput>
            </td>
          </tr>
        </table>
      </div>

      <div v-if="isForm" class="col col-sm-12 mb-2 text-start">
        <button @click="addMoreUnload" class="btn btn-red">Add More seed type weighing</button>
      </div>
    </div>
  </div>
</template>
