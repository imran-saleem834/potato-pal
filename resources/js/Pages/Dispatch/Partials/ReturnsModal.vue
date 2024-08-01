<script setup>
import { computed, watch } from 'vue';
import { DatePicker } from 'v-calendar';
import { useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import { getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  dispatch: {
    type: Object,
    default: null,
  },
  returns: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close']);

const form = useForm({
  dispatch: props.dispatch,
  created_at: props.returns?.returns?.created_at?.split('.')[0].replace('T', ' ') || '',
  docket_no: props.returns?.returns?.docket_no || '',
  comment: props.returns?.returns?.comment || '',
  half_tonnes: props.returns?.half_tonnes || '',
  one_tonnes: props.returns?.one_tonnes || '',
  two_tonnes: props.returns?.two_tonnes || ''
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

const updateRecord = () => {
  form.patch(route('returns.update', props.returns.returns.id), {
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
  const type = props.dispatch.dispatch_type;
  if (type === 'reallocation') {
    if (props.dispatch.item.foreignable.item.foreignable.type === 'sizing') {
      return props.dispatch.item.foreignable.item.foreignable.item.foreignable.allocatable.sizeable;
    }
    return props.dispatch.item.foreignable.item.foreignable.item.foreignable;
  } else if (type === 'cutting') {
    if (props.dispatch.item.foreignable.type === 'sizing') {
      return props.dispatch.item.foreignable.item.foreignable.allocatable.sizeable;
    }
    return props.dispatch.item.foreignable.item.foreignable;
  } else if (type === 'sizing') {
    return props.dispatch.item.foreignable.allocatable.sizeable;
  }

  return props.dispatch.item.foreignable;
});

watch(
  () => props.dispatch,
  (dispatch) => {
    if (dispatch) {
      form.dispatch = dispatch;
    }
  },
);

watch(
  () => props.returns,
  (returns) => {
    if (returns) {
      form.created_at = returns?.returns?.created_at?.split('.')[0].replace('T', ' ') || '';
      form.docket_no = returns?.returns?.docket_no || '';
      form.comment = returns?.returns?.comment || '';
      form.half_tonnes = returns?.half_tonnes || '';
      form.one_tonnes = returns?.one_tonnes || '';
      form.two_tonnes = returns?.two_tonnes || '';
    }
  },
);
</script>

<template>
  <div class="modal fade" id="returns-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 v-if="dispatch" class="modal-title" id="returns-modal-Label">Returns</h5>
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
                  <th>To</th>
                  <th>Grower Group</th>
                  <th>Grower</th>
                  <th>Paddock</th>
                  <th>Variety</th>
                  <th>Gen</th>
                  <th>Seed type</th>
                  <th>Class</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ dispatch.dispatch_type.toUpperCase() }}</td>
                  <td>
                    {{ getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-' }}
                  </td>
                  <td>{{ allocation.grower?.grower_name || '-' }}</td>
                  <td>{{ allocation.paddock }}</td>
                  <td>
                    {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}
                  </td>
                  <td>
                    {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}
                  </td>
                  <td>
                    <template v-if="dispatch.dispatch_type === 'cutting'">Cut Seed</template>
                    <template v-else-if="dispatch.dispatch_type === 'sizing'">
                      {{ getSingleCategoryNameByType(dispatch.item.foreignable.categories, 'seed-type') || '-' }}
                    </template>
                    <template v-else-if="dispatch.dispatch_type === 'reallocation'">
                      <template v-if="dispatch.item.foreignable.item.foreignable.type === 'sizing'">
                        {{
                          getSingleCategoryNameByType(
                            dispatch.item.foreignable.item.foreignable.item.foreignable.categories,
                            'seed-type',
                          ) || '-'
                        }}
                      </template>
                      <template v-else>
                        {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
                      </template>
                    </template>
                    <template v-else>
                      {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
                    </template>
                  </td>
                  <td>
                    {{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row my-3">
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <TextInput type="text" v-model="form.half_tonnes" :error="form.errors.half_tonnes">
                <template #prefix-addon>
                  <div class="input-group-text">Half tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <TextInput type="text" v-model="form.one_tonnes" :error="form.errors.one_tonnes">
                <template #prefix-addon>
                  <div class="input-group-text">One tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <TextInput type="text" v-model="form.two_tonnes" :error="form.errors.two_tonnes">
                <template #prefix-addon>
                  <div class="input-group-text">Two tonne</div>
                </template>
                <template #addon>
                  <div class="input-group-text">Bins</div>
                </template>
              </TextInput>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <label class="form-label">Return Time</label>
              <DatePicker
                v-model.string="form.created_at"
                mode="dateTime"
                :masks="{
                  modelValue: 'YYYY-MM-DD HH:mm:ss',
                }"
              >
                <template #default="{ togglePopover }">
                  <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors[`created_at`] }"
                    :value="form.created_at"
                    @click="togglePopover"
                  />
                  <div v-if="form.errors[`created_at`]" class="invalid-feedback" v-text="form.errors[`created_at`]" />
                </template>
              </DatePicker>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <label class="form-label">Docket No</label>
              <TextInput type="text" v-model="form.docket_no" :error="form.errors.docket_no" />
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
              <label class="form-label">Comments</label>
              <TextInput v-model="form.comment" :error="form.errors.comment" type="text" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button v-if="returns?.id" type="button" class="btn btn-red" @click="updateRecord">Save Return</button>
          <button v-else type="button" class="btn btn-red" @click="storeRecord">Save Return</button>
        </div>
      </div>
    </div>
  </div>
</template>
