<script setup>
import moment from 'moment';
import { computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import TextInput from '@/Components/TextInput.vue';
import Images from '@/Components/Images.vue';
import UlLiButton from '@/Components/UlLiButton.vue';
import { getCategoriesByType } from '@/helper.js';
import { binSizes, tiaStatus } from '@/const.js';

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
  if (values[4] && !values[4].toString().includes('%')) {
    values[4] = values[4] + '%';
  }
  if (values[5] && !values[5].toString().includes('%')) {
    values[5] = values[5] + '%';
  }
  return values;
};

const displaySampleValue = (name) => {
  if (name === 'tubers') {
    return getTotalTurbersValue();
  }

  const values = props.tiaSample[name];
  if (!values) return '';

  const length = values.length - 1;

  if (['sub_total', 'total_disease', 'total_defects'].includes(name)) {
    return values[length] || '-';
  }

  return (
    values
      .slice(0, length)
      .filter((val) => val !== '')
      .join(',  ') +
    ' - ' +
    values.slice(-1)
  );
};

const displaySampleValue2 = (name) => {
  const values = props.tiaSample[name];
  if (!values) return '';

  const length = values.length - 2;

  return (
    values
      .slice(0, length)
      .filter((val) => val !== '')
      .join(',  ') +
    ' - ' +
    values[length]
  );
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
  ['dry_rot', 'black_scurf', 'powdery_scab', 'root_knot_nematode', 'soft_rots', 'pink_rot'].forEach(
    (name) => {
      addition += parseFloat(form[name][length].replace('%', '') || 0);
    },
  );

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
  props.tiaSample[sample.name] = addEmptyValues(
    sample.name,
    props.tiaSample[sample.name] || [],
    sample.inputs || 5,
  );
});

const form = useForm({
  receival_id: props.tiaSample.receival_id,
  processor: props.tiaSample.processor,
  inspection_no: props.tiaSample.inspection_no,
  inspection_date: moment(props.tiaSample.inspection_date).format('YYYY-MM-DD'),
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
  disease_scoring: props.tiaSample.disease_scoring,
  disease_powdery_scab: props.tiaSample.disease_powdery_scab,
  disease_root_knot_nematode: props.tiaSample.disease_root_knot_nematode,
  disease_common_scab: props.tiaSample.disease_common_scab,
  excessive_dirt: props.tiaSample.excessive_dirt,
  minor_skin_cracking: props.tiaSample.minor_skin_cracking,
  skinning: props.tiaSample.skinning,
  regarding: props.tiaSample.regarding,
  comment: props.tiaSample.comment,
  status: props.tiaSample.receival?.tia_status,
});

watch(
  () => props.tiaSample,
  (tiaSample) => {
    form.clearErrors();
    form.receival_id = tiaSample.receival_id;
    form.processor = tiaSample.processor;
    form.inspection_no = tiaSample.inspection_no;
    form.inspection_date = moment(tiaSample.inspection_date).format('YYYY-MM-DD');
    form.size = tiaSample.size;
    form.disease_scoring = tiaSample.disease_scoring;
    form.excessive_dirt = tiaSample.excessive_dirt;
    form.minor_skin_cracking = tiaSample.minor_skin_cracking;
    form.skinning = tiaSample.skinning;
    form.regarding = tiaSample.regarding;
    form.comment = tiaSample.comment;
    form.status = tiaSample.receival?.tia_status;
    samples.concat(sample2).forEach((sample) => {
      form[sample.name] = addEmptyValues(
        sample.name,
        tiaSample[sample.name] || [],
        sample.inputs || 5,
      );
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
      <h4>Receival Details</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr v-if="isForm">
            <th>Receival</th>
            <td>
              <Multiselect
                v-model="form.receival_id"
                mode="single"
                placeholder="Choose a receival"
                :searchable="true"
                :options="receivals"
                :class="{ 'is-invalid': form.errors.receival_id }"
              />
              <div
                v-if="form.errors.receival_id"
                class="invalid-feedback"
                v-text="form.errors.receival_id"
              />
            </td>
          </tr>
          <template v-else>
            <tr>
              <th>Grower Name</th>
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
              <th>Receival id</th>
              <td>
                <Link
                  class="p-0"
                  v-if="tiaSample.receival?.id"
                  :href="route('receivals.index', { receivalId: tiaSample.receival.id })"
                >
                  {{ tiaSample.receival.id }}
                </Link>
                <template v-else>-</template>
              </td>
            </tr>
          </template>
          <tr v-if="!isNew">
            <th>Time added</th>
            <td>{{ moment(tiaSample.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
          </tr>
          <tr v-if="!isNew">
            <th>Grower Group</th>
            <td class="pb-0">
              <ul
                class="p-0"
                v-if="getCategoriesByType(tiaSample?.receival?.categories, 'grower-group').length"
              >
                <li
                  v-for="category in getCategoriesByType(
                    tiaSample?.receival?.categories,
                    'grower-group',
                  )"
                  :key="category.id"
                >
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="!isNew">
            <th>Cool Store</th>
            <td class="pb-0">
              <ul
                class="p-0"
                v-if="
                  getCategoriesByType(tiaSample?.receival?.grower?.categories, 'cool-store').length
                "
              >
                <li
                  v-for="category in getCategoriesByType(
                    tiaSample?.receival?.grower?.categories,
                    'cool-store',
                  )"
                  :key="category.id"
                >
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Status</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.status"
                :items="tiaStatus"
                @click="(key) => (form.status = key)"
              />
            </td>
          </tr>
        </table>
      </div>
    </div>

    <div class="col-12 col-xxl-6">
      <h4>Seed Details</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr v-if="!isNew">
            <th>Variety</th>
            <td class="pb-0">
              <ul
                class="p-0"
                v-if="getCategoriesByType(tiaSample?.receival?.categories, 'seed-variety').length"
              >
                <li
                  v-for="category in getCategoriesByType(
                    tiaSample?.receival?.categories,
                    'seed-variety',
                  )"
                  :key="category.id"
                >
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="!isNew">
            <th>Gen</th>
            <td class="pb-0">
              <ul
                class="p-0"
                v-if="
                  getCategoriesByType(tiaSample?.receival?.categories, 'seed-generation').length
                "
              >
                <li
                  v-for="category in getCategoriesByType(
                    tiaSample?.receival?.categories,
                    'seed-generation',
                  )"
                  :key="category.id"
                >
                  <a>{{ category.category.name }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="!isNew">
            <th>Growers’s Docket No</th>
            <td>
              <template v-if="tiaSample.receival?.grower_docket_no">
                {{ tiaSample.receival?.grower_docket_no }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Processor</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.processor"
                :error="form.errors.processor"
                :items="binSizes"
                @click="(value) => (form.processor = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Inspection No</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.inspection_no"
                :error="form.errors.inspection_no"
                type="text"
              />
              <template v-else-if="tiaSample.inspection_no">{{ tiaSample.inspection_no }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Inspection Date</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.inspection_date"
                :error="form.errors.inspection_date"
                type="date"
              />
              <template v-else-if="tiaSample.inspection_date">
                {{ moment(tiaSample.inspection_date).format('YYYY-MM-DD') }}
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
                      <td
                        :class="{ 'fw-bold': sample.bold }"
                        class="d-table-cell d-sm-none border-0 pb-0"
                      >
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
                  <ul v-if="isForm" class="multiple-inputs p-0 m-0">
                    <li
                      v-for="(value, index) in tiaSample[sample.name]"
                      :key="`${index}-${sample.name}`"
                      :class="sample.name"
                    >
                      <input
                        type="text"
                        class="form-control"
                        :disabled="index === 4"
                        v-model="form[sample.name][index]"
                        @keyup="changeSampleValue(sample.name, 4)"
                      />
                    </li>
                    <li class="d-none d-sm-inline-block">{{ sample.allow }}</li>

                    <template
                      v-for="(value, index) in tiaSample[sample.name]"
                      :key="`${index}-${sample.name}-error`"
                    >
                      <div class="w-100 text-start" v-if="form.errors[`${sample.name}.${index}`]">
                        <div class="is-invalid"></div>
                        <div class="invalid-feedback m-0 p-0">
                          {{ form.errors[`${sample.name}.${index}`] }}
                        </div>
                      </div>
                    </template>
                  </ul>
                  <template v-else>
                    {{ displaySampleValue(sample.name) }}
                  </template>
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
          <tr>
            <th class="fw-bold">DISEASE SCORING KEY</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.disease_scoring"
                :error="form.errors.disease_scoring"
                :items="[
                  { value: 1, label: '1' },
                  { value: 2, label: '2' },
                  { value: 3, label: '3' },
                  { value: 4, label: '4' },
                  { value: 5, label: '5' },
                  { value: 6, label: '6' },
                ]"
                @click="(value) => (form.disease_scoring = value)"
              />
            </td>
          </tr>
          <tr v-for="sample in sample2" :key="sample.name">
            <th>{{ sample.title }}</th>
            <td>
              <ul v-if="isForm" class="multiple-inputs p-0 m-0">
                <li
                  v-for="(value, index) in tiaSample[sample.name]"
                  :key="`${index}-${sample.name}`"
                  :class="sample.name"
                >
                  <input
                    type="text"
                    class="form-control"
                    :disabled="index === 4"
                    v-model="form[sample.name][index]"
                    @keyup="changeSampleValue(sample.name, 4)"
                  />
                </li>

                <template
                  v-for="(value, index) in tiaSample[sample.name]"
                  :key="`${index}-${sample.name}-error`"
                >
                  <div class="w-100 text-start" v-if="form.errors[`${sample.name}.${index}`]">
                    <div class="is-invalid"></div>
                    <div class="invalid-feedback m-0 p-0">
                      {{ form.errors[`${sample.name}.${index}`] }}
                    </div>
                  </div>
                </template>
              </ul>
              <template v-else>
                {{ displaySampleValue2(sample.name) }}
              </template>
            </td>
          </tr>
          <tr>
            <th>Excessive Dirt</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.excessive_dirt"
                :error="form.errors.excessive_dirt"
                :items="[
                  { value: true, label: 'Yes' },
                  { value: false, label: 'No' },
                ]"
                @click="(value) => (form.excessive_dirt = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Minor Skin Cracking</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.minor_skin_cracking"
                :error="form.errors.minor_skin_cracking"
                :items="[
                  { value: true, label: 'Yes' },
                  { value: false, label: 'No' },
                ]"
                @click="(value) => (form.minor_skin_cracking = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Skinning</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.skinning"
                :error="form.errors.skinning"
                :items="[
                  { value: true, label: 'Yes' },
                  { value: false, label: 'No' },
                ]"
                @click="(value) => (form.skinning = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Regarding</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.regarding"
                :error="form.errors.regarding"
                :items="[
                  { value: true, label: 'Yes' },
                  { value: false, label: 'No' },
                ]"
                @click="(value) => (form.regarding = value)"
              />
            </td>
          </tr>
          <tr>
            <th>Comment</th>
            <td class="pb-0">
              <TextInput
                v-if="isForm"
                v-model="form.comment"
                :error="form.errors.comment"
                type="text"
              />
              <template v-else-if="tiaSample.comment">{{ tiaSample.comment }}</template>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="!isNew">Images</h4>
      <div v-if="!isNew" class="user-boxes notes-list">
        <Images
          type="tia-samples"
          :id="tiaSample.id || 0"
          :images="tiaSample.images || []"
          @update="emit('update')"
        />
      </div>
    </div>
  </div>
</template>
