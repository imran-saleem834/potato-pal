<script setup>
import moment from 'moment';
import { useForm } from "@inertiajs/vue3";
import ConfirmedModal from '@/Components/ConfirmedModal.vue';

defineProps({
  items: Array,
  canEdit: {
    default: false,
  }
});

defineEmits(['edit']);

const form = useForm({});

const deleteReturn = (itemId) => {
  form.delete(route('returns.destroy', itemId), {
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<template>
  <template v-if="items.length">
    <div class="py-1 border-top">
      <span class="text-danger">Returns:</span>
    </div>
    <div class="position-relative" v-for="item in items" :key="item.id">
      <div v-if="canEdit" class="btn-group position-absolute top-0" style="right: -48px;">
        <button 
          data-bs-toggle="modal" 
          data-bs-target="#returns-modal" 
          @click="$emit('edit', item)" 
          class="btn btn-red p-1 z-1"
        >
          <i class="bi bi-pen"></i>
        </button>
        <button data-bs-toggle="modal" :data-bs-target="`#delete-return-${item.id}`" class="btn btn-red p-1 z-1">
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>
      
      <div class="row">
        <div v-if="item.half_tonnes > 0" class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Half Tonnes: </span>
          <span class="text-danger">{{ `${item.half_tonnes} Bins` }}</span>
        </div>
        <div v-if="item.one_tonnes > 0" class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>One Tonnes: </span>
          <span class="text-danger">{{ `${item.one_tonnes} Bins` }}</span>
        </div>
        <div v-if="item.two_tonnes > 0" class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Two Tonnes: </span>
          <span class="text-danger">{{ `${item.two_tonnes} Bins` }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Return Time: </span>
          <span class="text-danger">
            {{ moment(item.returns.created_at).format('DD/MM/YYYY hh:mm A') }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Docket No: </span>
          <span class="text-danger">{{ item.returns.docket_no }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Comments: </span>
          <span class="text-danger">{{ item.returns.comment }}</span>
        </div>
      </div>
      
      <ConfirmedModal
        :id="`delete-return-${item.id}`"
        cancel="No, Keep it"
        ok="Yes, Delete!"
        @ok="() => deleteReturn(item.returns.id)"
      />
    </div>
  </template>
</template>
