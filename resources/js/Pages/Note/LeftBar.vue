<script setup>
import moment from 'moment';

defineProps({
    items: Array,
    activeTab: {
        type: Number,
        default: null,
    },
    row1: Object,
    row2: Object,
});

const emit = defineEmits(['click']);

const menuClick = (id) => {
    emit('click', id);
};

const getObjectProperty = (obj, key) => {
    const keys = key.split('.');
    let result = obj;

    for (const k of keys) {
        if (result.hasOwnProperty(k)) {
            result = result[k];
        } else {
            // Property not found
            return undefined;
        }
    }

    return result;
};
</script>


<template>
    <ul class="nav nav-tabs tabs-left sideways">
        <li
            v-for="item in items"
            :key="item.id"
            :class="{'active' : activeTab === item.id}"
        >
            <a
                role="button"
                :data-toggle="$windowWidth <= 767 ? 'modal' : 'tab'"
                :data-target="$windowWidth <= 767 ? '#user-details' : ''"
                @click="menuClick(item.id)"
            >
                <div class="user-table">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th>{{ moment(item.created_at).format('DD, MMM YYYY') }}</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td v-text="getObjectProperty(item, row1.value)"/>
                            <td style="display: flex;justify-content: center;">
                                <template v-if="item.tags">
                                    <span v-for="tag in item.tags" :key="tag" class="tab-redbtn">{{ tag }}</span>
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <span class="fa fa-angle-right angle-right"></span>
                </div>
            </a>
        </li>
        <li
            v-if="items.length <= 0"
            style="box-shadow: none; text-align: center; margin-top: calc(50vh - 120px);"
        >No Records Found
        </li>
    </ul>
</template>
