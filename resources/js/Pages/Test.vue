<script setup>
import { reactive, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const channel = ref('BU1');

const message = reactive({
  'responseChannel': `receivalId`,
  'getLoggedWeights': "PotatoPal",
  'title': "greeting",
  'description': "description",
  'terminalCommand': "S", 
  'seed': "", 
  'bins': "", 
  'staffID': `${page.props.auth.user.id}`,
  'system': 1
  // 'weighTime': weighTime, 
  // 'receivalID': receivalID, 
  // 'offset': offset, 
  // 'nextrows': nextrows
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
    publishKey: "pub-c-09c14f75-bf91-4ab3-898c-ab220acf76ce",
    subscribeKey: "sub-c-a7835d02-324e-11e9-b681-be2e977db94e",
    userId: `${page.props.auth.user.id}`
  });

  const listener = {
    status: (statusEvent) => {
      console.log('statusEvent', statusEvent);
      if (statusEvent.category === "PNConnectedCategory") {
        console.log("Connected");
      }
    },
    message: (messageEvent) => {
      console.log(new Date());
      console.log('messageEvent', messageEvent);
      // showMessage(messageEvent.message.description);
    },
    presence: (presenceEvent) => {
      // handle presence
    }
  };
  pubnub.addListener(listener);

  pubnub.subscribe({
    channels: ['weighbridge', 'BU1', 'BU2', 'BU3']
  });
};

const publishMessage = async (message) => {
  // With the right payload, you can publish a message, add a reaction to a message,
  // send a push notification, or send a small payload called a signal.
  const publishPayload = {
    channel: 'BU1',
    message
  };
  await pubnub.publish(publishPayload);
}

// run after page is loaded
window.onload = setupPubNub;
</script>

<template>
  <div>
    <div style="padding: 50px">
      <br/>
      <br/>
      <button class="btn btn-primary" :class="{'btn-success' : channel === 'BU1'}" @click="() => channel = 'BU1'">
        BU1
      </button>
      <button class="btn btn-primary" :class="{'btn-success' : channel === 'BU2'}" @click="() => channel = 'BU2'">
        BU2
      </button>
      <button class="btn btn-primary" :class="{'btn-success' : channel === 'BU3'}" @click="() => channel = 'BU3'">
        BU3
      </button>
      <br/>
      <br/>
      <br/>
      <div>{{ message }}</div>
      <button @click="buttonClick">Send</button>
    </div>
  </div>
</template>