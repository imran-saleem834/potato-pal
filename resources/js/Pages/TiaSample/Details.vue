<script setup>
import moment from 'moment';
import { computed, watch } from 'vue';
import { useToast } from 'vue-toastification';
import { useForm, Link } from '@inertiajs/vue3';
import { tiaStatus, booleanArray } from '@/const.js';
import { toTonnes, getSingleCategoryNameByType } from '@/helper.js';
import Images from '@/Components/Images.vue';
import TextInput from '@/Components/TextInput.vue';
import UlLiButton from '@/Components/UlLiButton.vue';
import TdOfCategories from '@/Components/TdOfCategories.vue';
import CustomDatePicker from '@/Components/CustomDatePicker.vue';

const toast = useToast();

const props = defineProps({
  tiaSample: Object,
  isEdit: Boolean,
  isNew: Boolean,
  receivals: Array,
  categories: Array,
});

const emit = defineEmits(['update', 'create']);

const addEmptyValues = (name, values, noOfValues) => {
  const length = noOfValues - 1;
  for (let i = 0; i <= length; i++) {
    values[i] = values[i] || '';
  }
  if (name === 'tubers' && values[4]) {
    return values;
  }
  if (values[4] && !values[4].toString().includes('%') && noOfValues !== 6) {
    values[4] = values[4] + '%';
  }
  if (values[5] && !values[5].toString().includes('%') && noOfValues !== 6) {
    values[5] = values[5] + '%';
  }
  return values;
};

const sum = (accumulator, current) => parseFloat(accumulator) + parseFloat(current);
const round = (num, decimalPlaces = 2) => {
  const p = Math.pow(10, decimalPlaces);
  return Math.round(num * p) / p;
};

const changeSampleValue = (name, length) => {
  const input = form[name]
    .slice(0, length)
    .filter((val) => val !== '')
    .reduce(sum, 0);

  if (name === 'tubers') {
    form[name][length] = input;
    return;
  }

  const totalTurbers = getTotalTurbersValue();
  form[name][length] = totalTurbers ? round((input * 100) / getTotalTurbersValue()) + '%' : '';

  console.log('changeSampleValue', name, form[name][length]);

  updateTotal(length);
};

const updateTotal = (length) => {
  let addition = 0;
  ['dry_rot', 'black_scurf', 'powdery_scab', 'root_knot_nematode', 'soft_rots', 'pink_rot'].forEach((name) => {
    addition += parseFloat(form[name][length].replace('%', '') || 0);
  });

  form['sub_total'][length] = round(addition) + '%';

  addition += parseFloat(form['common_scab'][length].replace('%', '') || 0);

  form['total_disease'][length] = round(addition) + '%';

  [
    'black_scurf_scatter',
    'insect_damage',
    'malformed_tubers',
    'mechanical_damage',
    'stem_end_discolour',
    'foreign_cultivars',
    'misc_frost',
  ].forEach((name) => {
    addition += parseFloat(form[name][length].replace('%', '') || 0);
  });

  form['total_defects'][length] = round(addition) + '%';
};

const getTotalTurbersValue = () => {
  return form.tubers
    .slice(0, 4)
    .filter((val) => val !== '')
    .reduce(sum, 0);
};

const samples = [
  { title: 'No. of tubers Samples', name: 'tubers', allow: 'Allowable tolerances' },
  { title: 'Dry Rot', name: 'dry_rot', allow: '2%' },
  { title: 'Black Scurf 2mm', name: 'black_scurf', allow: '2%' },
  { title: 'Powdery Scab', name: 'powdery_scab', allow: '2%' },
  { title: 'Root Knot Nematode', name: 'root_knot_nematode', allow: '2%' },
  { title: 'Soft Rots', name: 'soft_rots', allow: '0.25%' },
  { title: 'Pink Rot', name: 'pink_rot', allow: '0.25%' },
  { title: 'Sub Total', bold: true, name: 'sub_total', allow: '2%' },
  { title: 'Common Scab', name: 'common_scab', allow: '4% (2%)' },
  { title: 'Total Disease', bold: true, name: 'total_disease', allow: '4% (2%)' },
  { title: 'Black Scurf Scatter', name: 'black_scurf_scatter', allow: '10%' },
  { title: 'Other Tuber Defects' },
  { title: 'Insect Damage', name: 'insect_damage', allow: '1.5%' },
  { title: 'Malformed Tubers', name: 'malformed_tubers', allow: '2%' },
  { title: 'Mechanical Damage', name: 'mechanical_damage', allow: '2%' },
  { title: 'Stem End Discolour', name: 'stem_end_discolour', allow: '2%' },
  { title: 'Foreign Cultivars', name: 'foreign_cultivars', allow: '0%' },
  { title: 'Misc. Frost', name: 'misc_frost', allow: '1%' },
  { title: 'Total Defects', bold: true, name: 'total_defects', allow: '2%' },
  { title: 'Minimal Insect Feeding', name: 'minimal_insect_feeding', allow: 'Additional 2%' },
  { title: 'Oversize', name: 'oversize', allow: '' },
  { title: 'Undersize', name: 'undersize', allow: '' },
];

const sample2 = [
  { title: 'Powdery Scab', name: 'disease_powdery_scab', allow: '', inputs: 6 },
  { title: 'Rootknot Nematode', name: 'disease_root_knot_nematode', allow: '', inputs: 6 },
  { title: 'Common Scab', name: 'disease_common_scab', allow: '', inputs: 6 },
];

samples.concat(sample2).forEach((sample) => {
  props.tiaSample[sample.name] = addEmptyValues(sample.name, props.tiaSample[sample.name] || [], sample.inputs || 5);
});

const form = useForm({
  inspection_date: props.tiaSample.inspection_date
    ? props.tiaSample.inspection_date.split('T')[0]
    : new Date().toJSON().split('T')[0],
  size: props.tiaSample.size,
  tubers: props.tiaSample.tubers,
  dry_rot: props.tiaSample.dry_rot,
  black_scurf: props.tiaSample.black_scurf,
  powdery_scab: props.tiaSample.powdery_scab,
  root_knot_nematode: props.tiaSample.root_knot_nematode,
  soft_rots: props.tiaSample.soft_rots,
  pink_rot: props.tiaSample.pink_rot,
  sub_total: props.tiaSample.sub_total,
  common_scab: props.tiaSample.common_scab,
  total_disease: props.tiaSample.total_disease,
  black_scurf_scatter: props.tiaSample.black_scurf_scatter,
  insect_damage: props.tiaSample.insect_damage,
  malformed_tubers: props.tiaSample.malformed_tubers,
  mechanical_damage: props.tiaSample.mechanical_damage,
  stem_end_discolour: props.tiaSample.stem_end_discolour,
  foreign_cultivars: props.tiaSample.foreign_cultivars,
  misc_frost: props.tiaSample.misc_frost,
  total_defects: props.tiaSample.total_defects,
  minimal_insect_feeding: props.tiaSample.minimal_insect_feeding,
  oversize: props.tiaSample.oversize,
  undersize: props.tiaSample.undersize,
  disease_powdery_scab: props.tiaSample.disease_powdery_scab,
  disease_root_knot_nematode: props.tiaSample.disease_root_knot_nematode,
  disease_common_scab: props.tiaSample.disease_common_scab,
  excessive_dirt: props.tiaSample.excessive_dirt,
  skin_russeting: props.tiaSample.skin_russeting,
  minor_skin_cracking: props.tiaSample.minor_skin_cracking,
  silver_scurf: props.tiaSample.silver_scurf,
  skinning: props.tiaSample.skinning,
  black_dot: props.tiaSample.black_dot,
  regrading: props.tiaSample.regrading,
  comment: props.tiaSample.comment,
  status: props.tiaSample.status,
});

watch(
  () => props.tiaSample,
  (tiaSample) => {
    form.clearErrors();
    form.inspection_date = tiaSample.inspection_date
      ? tiaSample.inspection_date.split('T')[0]
      : new Date().toJSON().split('T')[0];
    form.size = tiaSample.size;
    form.excessive_dirt = tiaSample.excessive_dirt;
    form.skin_russeting = tiaSample.skin_russeting;
    form.minor_skin_cracking = tiaSample.minor_skin_cracking;
    form.silver_scurf = tiaSample.silver_scurf;
    form.skinning = tiaSample.skinning;
    form.black_dot = tiaSample.black_dot;
    form.regrading = tiaSample.regrading;
    form.comment = tiaSample.comment;
    form.status = tiaSample.status;
    samples.concat(sample2).forEach((sample) => {
      form[sample.name] = addEmptyValues(sample.name, tiaSample[sample.name] || [], sample.inputs || 5);
    });
  },
);

watch(
  () => props.isNew,
  (isNew) => {
    if (isNew) {
      samples.concat(sample2).forEach((sample) => {
        props.tiaSample[sample.name] = addEmptyValues(sample.name, [], sample.inputs || 5);
      });
    }
  },
);

const isForm = computed(() => props.isEdit || props.isNew);

const updateRecord = () => {
  form.patch(route('tia-samples.update', props.tiaSample.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The tia sample has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('tia-samples.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The tia sample has been created successfully!');
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
    <div class="col-12 col-xxl-6">
      <h4>Grower Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Grower</th>
            <td>
              <Link
                class="p-0"
                v-if="tiaSample.receival?.id"
                :href="route('users.index', { userId: tiaSample.receival.grower.id })"
              >
                {{ tiaSample.receival.grower?.grower_name }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Paddock</th>
            <td>
              <Link
                class="p-0"
                v-if="tiaSample.receival?.id"
                :href="route('receivals.index', { receivalId: tiaSample.receival.id })"
              >
                {{ tiaSample.receival.paddocks[0] }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Date Unloaded</th>
            <td>{{ moment(tiaSample.receival.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
          </tr>
          <tr>
            <th>Grower Group</th>
            <TdOfCategories :categories="tiaSample?.receival?.categories" type="grower-group" />
          </tr>
          <tr>
            <th>Cool Store</th>
            <TdOfCategories :categories="tiaSample?.receival?.grower?.categories" type="cool-store" />
          </tr>
        </table>
      </div>
    </div>

    <div class="col-12 col-xxl-6">
      <h4>Seed Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Variety</th>
            <TdOfCategories :categories="tiaSample?.receival?.categories" type="seed-variety" />
          </tr>
          <tr>
            <th>Gen</th>
            <TdOfCategories :categories="tiaSample?.receival?.categories" type="seed-generation" />
          </tr>
          <tr>
            <th>Growers’s Docket No</th>
            <td>
              <template v-if="tiaSample.receival?.grower_docket_no">
                {{ tiaSample.receival?.grower_docket_no }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-for="unload in tiaSample?.receival?.unloads || []" :key="unload.id">
            <td>{{ getSingleCategoryNameByType(unload.categories, 'seed-type') }}</td>
            <td>{{ `${unload.no_of_bins} Bins, Weight: ${toTonnes(unload.weight)}` }}</td>
          </tr>
          <tr>
            <th>Inspection ID</th>
            <td>{{ tiaSample.id }}</td>
          </tr>
          <tr>
            <th>Inspection Date</th>
            <td>
              <CustomDatePicker v-if="isForm" :form="form" field="inspection_date" mode="date" format="YYYY-MM-DD" />
              <template v-else-if="tiaSample.inspection_date">
                {{ moment(tiaSample.inspection_date).format('DD/MM/YYYY') }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <div class="col-12 col-xxl-6">
      <h4>TIA Seed Potato Certificate Sheet</h4>
      <div class="user-boxes table-responsive">
        <table class="table input-table mb-0">
          <tr class="d-table-row d-sm-none border-0">
            <th colspan="2" class="fw-bold border-0">Size</th>
          </tr>
          <tr>
            <th class="fw-bold d-none d-sm-table-cell">Size</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.size"
                :error="form.errors.size"
                :items="[
                  { value: '35-350g', label: '35 - 350g' },
                  { value: '90mm', label: '90mm' },
                  { value: '70mm', label: '70mm' },
                ]"
                @click="(value) => (form.size = value)"
              />
            </td>
          </tr>
          <template v-for="sample in samples" :key="sample.name">
            <template v-if="sample.name">
              <tr class="d-table-row d-sm-none border-0">
                <td colspan="2" class="p-0 m-0">
                  <table class="table table-borderless p-0 m-0">
                    <tr class="border-0">
                      <td :class="{ 'fw-bold': sample.bold }" class="d-table-cell d-sm-none border-0 pb-0">
                        {{ sample.title }}
                      </td>
                      <td class="pb-0 text-end" style="width: 40%">{{ sample.allow }}</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <th :class="{ 'fw-bold': sample.bold }" class="d-none d-sm-table-cell">
                  {{ sample.title }}
                </th>
                <td>
                  <ul class="multiple-inputs sample-inputs p-0 m-0">
                    <li
                      v-for="(value, index) in form[sample.name]"
                      :key="`${index}-${sample.name}`"
                      :class="sample.name"
                    >
                      <input
                        type="text"
                        class="form-control"
                        :disabled="index === 4 || !isForm"
                        v-model="form[sample.name][index]"
                        @keyup="changeSampleValue(sample.name, 4)"
                      />
                    </li>
                    <li class="d-none d-sm-inline-block">{{ sample.allow }}</li>

                    <template v-for="(value, index) in form[sample.name]" :key="`${index}-${sample.name}-error`">
                      <div class="w-100 text-start" v-if="form.errors[`${sample.name}.${index}`]">
                        <div class="is-invalid"></div>
                        <div class="invalid-feedback m-0 p-0">
                          {{ form.errors[`${sample.name}.${index}`] }}
                        </div>
                      </div>
                    </template>
                  </ul>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <th colspan="2" class="fw-bold fs-6">{{ sample.title }}</th>
              </tr>
            </template>
          </template>
        </table>
      </div>
    </div>

    <div class="col-12 col-xxl-6">
      <h4>Continue External Report</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr class="d-table-row d-sm-none border-0">
            <th colspan="2" class="fw-bold border-0">DISEASE SCORING KEY</th>
          </tr>
          <tr>
            <th class="fw-bold d-none d-sm-table-cell">DISEASE SCORING KEY</th>
            <td class="pb-0 disease-scoring-key" colspan="3">
              <UlLiButton
                :is-form="false"
                :items="[
                  { value: 1, label: '1' },
                  { value: 2, label: '2' },
                  { value: 3, label: '3' },
                  { value: 4, label: '4' },
                  { value: 5, label: '5' },
                  { value: 6, label: '6' },
                ]"
              />
            </td>
          </tr>
          <template v-for="sample in sample2" :key="sample.name">
            <tr class="d-table-row d-sm-none border-0">
              <td colspan="2" class="fw-bold border-0" :class="{ 'fw-bold': sample.bold }">
                {{ sample.title }}
              </td>
            </tr>
            <tr>
              <th class="d-none d-sm-table-cell">{{ sample.title }}</th>
              <td colspan="3">
                <ul class="multiple-inputs p-0 m-0">
                  <li v-for="(value, index) in form[sample.name]" :key="`${index}-${sample.name}`" :class="sample.name">
                    <input type="text" class="form-control" :disabled="!isForm" v-model="form[sample.name][index]" />
                  </li>

                  <template v-for="(value, index) in form[sample.name]" :key="`${index}-${sample.name}-error`">
                    <div class="w-100 text-start" v-if="form.errors[`${sample.name}.${index}`]">
                      <div class="is-invalid"></div>
                      <div class="invalid-feedback m-0 p-0">
                        {{ form.errors[`${sample.name}.${index}`] }}
                      </div>
                    </div>
                  </template>
                </ul>
              </td>
            </tr>
          </template>
          <tr>
            <th>Excessive Dirt</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.excessive_dirt"
                :error="form.errors.excessive_dirt"
                :items="booleanArray"
                @click="(value) => (form.excessive_dirt = value)"
                style="width: 130px"
              />
            </td>
            <th class="d-none d-sm-table-cell">Skin russeting</th>
            <td class="d-none d-sm-table-cell pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.skin_russeting"
                :error="form.errors.skin_russeting"
                :items="booleanArray"
                @click="(value) => (form.skin_russeting = value)"
              />
            </td>
          </tr>
          <tr class="d-table-row d-sm-none">
            <th>Skin russeting</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.skin_russeting"
                :error="form.errors.skin_russeting"
                :items="booleanArray"
                @click="(value) => (form.skin_russeting = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Minor Skin Cracking</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.minor_skin_cracking"
                :error="form.errors.minor_skin_cracking"
                :items="booleanArray"
                @click="(value) => (form.minor_skin_cracking = value)"
              />
            </td>
            <th class="d-none d-sm-table-cell">Silver scurf</th>
            <td class="d-none d-sm-table-cell pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.silver_scurf"
                :error="form.errors.silver_scurf"
                :items="booleanArray"
                @click="(value) => (form.silver_scurf = value)"
              />
            </td>
          </tr>
          <tr class="d-table-row d-sm-none">
            <th>Silver scurf</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.silver_scurf"
                :error="form.errors.silver_scurf"
                :items="booleanArray"
                @click="(value) => (form.silver_scurf = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Skinning</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.skinning"
                :error="form.errors.skinning"
                :items="booleanArray"
                @click="(value) => (form.skinning = value)"
              />
            </td>
            <th class="d-none d-sm-table-cell">Black dot</th>
            <th class="d-none d-sm-table-cell pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.black_dot"
                :error="form.errors.black_dot"
                :items="booleanArray"
                @click="(value) => (form.black_dot = value)"
              />
            </th>
          </tr>
          <tr class="d-table-row d-sm-none">
            <th>Black dot</th>
            <th class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.black_dot"
                :error="form.errors.black_dot"
                :items="booleanArray"
                @click="(value) => (form.black_dot = value)"
              />
            </th>
          </tr>
          <tr>
            <th>Regrading</th>
            <td class="pb-0" colspan="3">
              <UlLiButton
                :is-form="isForm"
                :value="!!form.regrading"
                :error="form.errors.regrading"
                :items="booleanArray"
                @click="(value) => (form.regrading = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Comment</th>
            <td colspan="3">
              <TextInput v-if="isForm" v-model="form.comment" :error="form.errors.comment" type="text" />
              <template v-else-if="tiaSample.comment">{{ tiaSample.comment }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Inspection Result</th>
            <td class="pb-0" colspan="3">
              <UlLiButton
                :is-form="isForm"
                :value="form.status"
                :items="tiaStatus"
                :error="form.errors.status"
                @click="(key) => (form.status = key)"
              />
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="!isNew">Images</h4>
      <div v-if="!isNew" class="user-boxes notes-list">
        <Images type="tia-samples" :id="tiaSample.id || 0" :images="tiaSample.images || []" @update="emit('update')" />
      </div>
    </div>
  </div>
</template>
