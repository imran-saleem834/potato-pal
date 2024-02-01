<script setup>
import moment from 'moment';
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import UlLiButton from "@/Components/UlLiButton.vue";
import Multiselect from '@vueform/multiselect';
import { useToast } from "vue-toastification";
import { binSizes, getBinSizesValue } from "@/tonnes.js";
import {
  toCamelCase,
  getSingleCategoryNameByType,
  getCategoriesDropDownByType,
  getCategoriesByType,
  getCategoryIdsByType,
} from "@/helper.js";

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

watch(() => props.receival,
  (receival) => {
    form.clearErrors();

    updateUnloadsOnChangeReceival(receival);
  }
);

const defaultUnloadFields = {
  seed_type: null,
  bin_size: null,
  no_of_bins: 0,
  weight: 0,
  channel: null,
  system: null,
  isSeedTypeOversize: false
};

const resetBinsAndWeight = (index) => {
  form.unloads[index].no_of_bins = null;
  form.unloads[index].weight = null;
}

const onChangeChannel = (value, index) => {
  form.unloads[index].channel = value;
  form.unloads[index].bin_size = value === 'weighbridge' ? 1000 : 2000;

  resetBinsAndWeight(index);
}

const onChangeSeedType = (value, index) => {
  form.unloads[index].seed_type = value;
  setIsSeedTypeOversize(form.unloads[index].seed_type, index);

  resetBinsAndWeight(index);
}

const setIsSeedTypeOversize = (seedTypeId, index) => {
  const selectedCategoryLabel = getCategoriesDropDownByType(props.categories, 'seed-type')
    .find(category => category.value === seedTypeId)?.label
  
  form.unloads[index].isSeedTypeOversize = (selectedCategoryLabel === 'Oversize');
}

const updateUnloadsOnChangeReceival = (receival) => {
  form.fungicide = getCategoryIdsByType(receival.categories, 'fungicide')
  form.unloads = receival.unloads.length <= 0 ? [{ ...defaultUnloadFields }] : receival.unloads;

  form.unloads.forEach((unload, index) => {
    form.unloads[index].seed_type = getCategoryIdsByType(unload.categories, 'seed-type')[0];
    setIsSeedTypeOversize(form.unloads[index].seed_type, index);

    bins.value[index] = null;
    weight.value[index] = null;
    weightError.value[index] = null;
  });
}

const onChangeTotalBins = (index) => {
  if (!form.unloads[index].isSeedTypeOversize) {
    return;
  }
  const oversizeSeedWeightOneTonne = 920;
  if (form.unloads[index].bin_size === 500) {
    form.unloads[index].weight = form.unloads[index].no_of_bins * (oversizeSeedWeightOneTonne / 2)
  }
  if (form.unloads[index].bin_size === 1000) {
    form.unloads[index].weight = form.unloads[index].no_of_bins * oversizeSeedWeightOneTonne
  }
  if (form.unloads[index].bin_size === 2000) {
    form.unloads[index].weight = form.unloads[index].no_of_bins * (oversizeSeedWeightOneTonne * 2)
  }
}

const onChangeBinSize = (value, index) => {
  form.unloads[index].bin_size = value;

  if (!form.unloads[index].isSeedTypeOversize) {
    resetBinsAndWeight(index);
  }
  
  onChangeTotalBins(index);
}

const onChangeSystem = (value, index) => {
  form.unloads[index].system = value;
  
  resetBinsAndWeight(index);
}

updateUnloadsOnChangeReceival(props.receival);

const addMoreUnload = () => form.unloads.push({ ...defaultUnloadFields });

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
  startWeighing: "PotatoPal",
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
    message: getWeightMessage
  };
  window.pubnub.instance.publish(publishPayload);
}

const startWeighing = (index) => {
  startWeighingMessage.responseChannel = window.pubnub.deviceId;
  startWeighingMessage.taskStartDate = new Date();
  startWeighingMessage.numberOfBinsToWeigh = bins.value[index];
  const publishPayload = {
    channel: form.unloads[index].channel,
    message: startWeighingMessage
  };
  window.pubnub.instance.publish(publishPayload);
}

onMounted(() => {
  document.addEventListener('GetWeight', (event) => {
    console.log('WeightReceived', event?.detail);
    weight.value[getWeightIndex.value] = event?.detail?.CurrentWeight?.toFixed(2);
    getWeightIndex.value = null;
  })
})

const acceptWeight = (index) => {
  form.unloads[index].no_of_bins = parseInt(form.unloads[index].no_of_bins ?? 0) + parseInt(bins.value[index]);
  form.unloads[index].weight = parseFloat(form.unloads[index].weight ?? 0) + parseFloat(weight.value[index]);

  rejectWeight(index);
}

const rejectWeight = (index) => {
  bins.value[index] = null;
  weight.value[index] = null;
}

const isForm = computed(() => props.isEdit);
const growerName = computed(() => {
  return props.receival.grower.name + (props.receival.grower?.grower_name ? ' (' + props.receival.grower?.grower_name + ')' : '');
});

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
  storeRecord
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
                {{ growerName }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Receival Id</th>
            <td>
              <Link class="p-0" :href="route('receivals.index', {receivalId: receival.id})">
                {{ receival.id }}
              </Link>
            </td>
          </tr>
          <tr>
            <th>Time Added</th>
            <td>{{ moment(receival.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
          </tr>
          <tr>
            <th>Grower Group</th>
            <td class="pb-0">
              <ul class="p-0" v-if="getCategoriesByType(receival.categories, 'grower-group').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'grower-group')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
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
            <th>Seed Variety</th>
            <td class="pb-0">
              <ul class="p-0" v-if="getCategoriesByType(receival.categories, 'seed-variety').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'seed-variety')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Seed Generation</th>
            <td class="pb-0">
              <ul class="p-0" v-if="getCategoriesByType(receival.categories, 'seed-generation').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'seed-generation')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>TIA Sampling</th>
            <td class="pb-0">
              <ul class="p-0">
                <li>
                  <button
                    v-if="receival.tia_sample"
                    :class="(receival.tia_sample?.status || 'pending') === 'pending' ? 'btn btn-pending' : 'btn btn-dark'"
                  >
                    {{ toCamelCase(receival.tia_sample?.status || 'pending') }}
                  </button>
                  <template v-else>-</template>
                </li>
              </ul>
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
                v-if="isForm"
                :is-form="true"
                :value="form.status"
                :error="form.errors.status"
                :items="[
                  { value: 'pending', label: 'Pending' },
                  { value: 'completed', label: 'Completed' },
                ]"
                @click="(value) => form.status = value"
              />
              <ul v-else class="p-0">
                <li>
                  <a role="button" :class="{'btn-pending' : form.status === 'pending'}">
                    {{ toCamelCase(form.status) }}
                  </a>
                </li>
              </ul>
            </td>
          </tr>
          <tr>
            <th>Fungicide</th>
            <td :class="{'pb-0' : !isForm}">
              <Multiselect
                v-if="isForm"
                v-model="form.fungicide"
                mode="tags"
                placeholder="Choose a fungicide"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'fungicide')"
                :class="{'is-invalid' : form.errors.fungicide}"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(receival.categories, 'fungicide').length">
                <li v-for="category in getCategoriesByType(receival.categories, 'fungicide')" :key="category.id">
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
              <div v-if="form.errors.fungicide" class="invalid-feedback" v-text="form.errors.fungicide"/>
            </td>
          </tr>
        </table>
      </div>
      <div v-for="(unload, index) in form.unloads" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Seed Type</th>
            <td class="pb-0">
              <UlLiButton
                v-if="isForm && form.unloads[index]"
                :is-form="true"
                :value="form.unloads[index].seed_type"
                :error="form.errors[`unloads.${index}.seed_type`]"
                :items="getCategoriesDropDownByType(categories, 'seed-type')"
                @click="(value) => onChangeSeedType(value, index)"
              />
              <ul class="p-0" v-else-if="getCategoriesByType(unload.categories, 'seed-type').length">
                <li><a>{{ getSingleCategoryNameByType(unload.categories, 'seed-type') }}</a></li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="!form.unloads[index]?.isSeedTypeOversize">
            <th>Channel</th>
            <td class="pb-0">
              <UlLiButton
                v-if="isForm && form.unloads[index]"
                :is-form="true"
                :value="form.unloads[index].channel"
                :error="form.errors[`unloads.${index}.channel`]"
                :items="[
                  { value: 'weighbridge', label: 'BU1' },
                  { value: 'BU2', label: 'BU2' },
                  { value: 'BU3', label: 'BU3' },
                ]"
                @click="(value) => onChangeChannel(value, index)"
              />
              <ul class="p-0" v-else-if="unload.channel">
                <li><a>{{ unload.channel === 'weighbridge' ? 'BU1' : unload.channel.toUpperCase() }}</a></li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Bin Size</th>
            <td class="pb-0">
              <UlLiButton
                v-if="isForm && form.unloads[index]"
                :is-form="true"
                :value="form.unloads[index].bin_size"
                :error="form.errors[`unloads.${index}.bin_size`]"
                :items="binSizes"
                @click="(value) => onChangeBinSize(value, index)"
              />
              <ul class="p-0" v-else-if="unload.bin_size">
                <li><a>{{ getBinSizesValue(unload.bin_size) }}</a></li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="!form.unloads[index]?.isSeedTypeOversize">
            <th>System</th>
            <td class="pb-0">
              <UlLiButton
                v-if="isForm && form.unloads[index]"
                :is-form="true"
                :value="form.unloads[index].system"
                :error="form.errors[`unloads.${index}.system`]"
                :items="
                  form.unloads[index].channel === 'weighbridge' ? 
                  [ { value: 1, label: 'System 1' } ] : 
                  [ { value: 1, label: 'System 1' }, { value: 2, label: 'System 2' } ]
                "
                @click="(value) => onChangeSystem(value, index)"
              />
              <ul class="p-0" v-else-if="unload.system">
                <li><a>{{ `System ${unload.system}` }}</a></li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>No. of total bins</th>
            <td class="pb-0">
              <input
                type="text"
                class="form-control"
                v-if="isForm && form.unloads[index]"
                v-model="form.unloads[index].no_of_bins"
                :class="{'is-invalid' : form.errors[`unloads.${index}.no_of_bins`]}"
                :disabled="!form.unloads[index]?.isSeedTypeOversize"
                @keyup="() => onChangeTotalBins(index)"
              >
              <template v-else-if="unload.no_of_bins">{{ unload.no_of_bins }}</template>
              <template v-else>-</template>
              <div v-if="form.errors[`unloads.${index}.no_of_bins`]" class="invalid-feedback">
                {{ form.errors[`unloads.${index}.no_of_bins`] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Weight of total bins</th>
            <td class="pb-0">
              <TextInput
                v-if="isForm && form.unloads[index]"
                :disabled="true"
                v-model="form.unloads[index].weight"
                :error="form.errors[`unloads.${index}.weight`]"
                type="text"
              />
              <template v-else-if="unload.weight">{{ unload.weight }} Kg</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="isForm && form.unloads[index] && !form.unloads[index]?.isSeedTypeOversize">
            <th>Number of bins</th>
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
                  @click="(value) => bins[index] = value"
                />
              </div>
              <div class="d-inline-block align-top">
                <button @click="startWeighing(index)" class="btn btn-red me-2">Start Weight</button>
                <div v-if="weight[index] && bins[index]" class="btn-group">
                  <button @click="acceptWeight(index)" class="btn btn-black"><i class="bi bi-check-lg"></i></button>
                  <button @click="rejectWeight(index)" class="btn btn-red"><i class="bi bi-trash"></i></button>
                </div>
                <button v-else @click="getWeight(index)" class="btn btn-red">Get Weight</button>
              </div>
            </td>
          </tr>
          <tr v-if="isForm && form.unloads[index] && !form.unloads[index]?.isSeedTypeOversize">
            <th>Weight of total bins</th>
            <td class="pb-0">
              <input
                :disabled="true"
                :value="weight[index]"
                class="form-control"
                type="text"
              >
            </td>
          </tr>
        </table>
      </div>

      <div v-if="isForm" class="col col-sm-12 mb-2 text-end">
        <button @click="addMoreUnload" class="btn btn-red">Add More seed type weighing</button>
      </div>
    </div>
  </div>
</template>
