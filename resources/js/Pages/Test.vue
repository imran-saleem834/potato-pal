<script setup>
import { reactive, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const channel = ref('BU1');

const userAgent = navigator.userAgent;
// You might include more information for uniqueness if needed
const additionalInfo = '';

// Hash the information to create a unique identifier
const deviceId = btoa(userAgent + additionalInfo);

// const message = reactive({
//   binSize: '12',
//   emptyBinWeight: '120',
//   exWhomGroupName: 'Grower 1',
//   exWhomName: 'Sam',
//   fungicide: 'fungicide 1',
//   numberOfBinsToWeigh: '2',
//   potIdentifierCode: 'BU2',
//   receivalID: '3',
//   // responseChannel: deviceId,
//   responseChannel: 'BU2',
//   seedType: 'OVERSIZE',
//   staffID: '9',
//   startWeighing: "PotatoPal",
//   taskOwner: 'Bulk Unloading',
//   taskStartDate: '2024-01-09',
//   taskStatus: "Pending",
// });
const message = reactive({
  seed: '',
  bins: '',
  responseChannel: deviceId,
  staffID: '9',
  system: '1',
  terminalCommand: 'S',
});

const buttonClick = () => {
  console.log(new Date());
  publishMessage(message);
};

// const showMessage = (msg) => {
//   message.value = msg;
// };

let pubnub;

const setupPubNub = () => {
  // Update this block with your publish/subscribe keys
  pubnub = new PubNub({
    publishKey: 'pub-c-09c14f75-bf91-4ab3-898c-ab220acf76ce',
    subscribeKey: 'sub-c-a7835d02-324e-11e9-b681-be2e977db94e',
    // userId: `${page.props.auth.user.id}`,
    uuid: deviceId,
  });

  const listener = {
    status: (statusEvent) => {
      console.log('statusEvent', statusEvent);
      if (statusEvent.category === 'PNConnectedCategory') {
        console.log('Connected');
      }
    },
    message: (messageEvent) => {
      console.log(new Date());
      console.log('messageEvent', messageEvent);
      // showMessage(messageEvent.message.description);
    },
    presence: (presenceEvent) => {
      // handle presence
    },
  };
  pubnub.addListener(listener);

  pubnub.subscribe({
    channels: [deviceId, 'BU2'],
  });
};

const publishMessage = async (message) => {
  // With the right payload, you can publish a message, add a reaction to a message,
  // send a push notification, or send a small payload called a signal.
  const publishPayload = {
    // channel: 'weighbridge', // 51
    channel: 'BU2', // System 1: 20, System 2: 20
    // channel: 'BU3', // System 1: 12-12, System 2: 36
    message,
  };
  await pubnub.publish(publishPayload);
};

// run after page is loaded
window.onload = setupPubNub;
</script>

<template>
  <div>
    <div style="padding: 50px">
      <br />
      <br />
      <button class="btn btn-primary" :class="{ 'btn-success': channel === 'BU1' }" @click="() => (channel = 'BU1')">
        BU1
      </button>
      <button class="btn btn-primary" :class="{ 'btn-success': channel === 'BU2' }" @click="() => (channel = 'BU2')">
        BU2
      </button>
      <button class="btn btn-primary" :class="{ 'btn-success': channel === 'BU3' }" @click="() => (channel = 'BU3')">
        BU3
      </button>
      <br />
      <br />
      <br />
      <div>{{ message }}</div>
      <button @click="buttonClick">Send</button>
    </div>
  </div>
</template>
