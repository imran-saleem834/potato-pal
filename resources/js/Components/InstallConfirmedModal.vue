<script setup>
import { ref, onMounted, computed } from "vue";

let deferredPrompt;
const showInstallModal = ref(true);
const installBtn = ref(true);

const install = () => {
  deferredPrompt.prompt();

  deferredPrompt.userChoice.then((choiceResult) => {
    if (choiceResult.outcome === 'accepted') {
      console.log('User accepted the install prompt');
    } else {
      console.log('User dismissed the install prompt');
    }
    deferredPrompt = null;
  });
};

const isIOS = computed(() => {
  const userAgent = window.navigator.userAgent.toLowerCase();
  return /iphone|ipad|ipod/.test(userAgent);
});

onMounted(() => {
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    installBtn.value.click();
  });

  window.addEventListener('appinstalled', (evt) => {
    deferredPrompt = null;
  });

  if (isIOS && window.matchMedia('(display-mode: browser)').matches) {
    installBtn.value.click();
  }
});
</script>

<template>
  <div data-bs-target="#install-app" data-bs-toggle="modal" ref="installBtn" class="d-none">Install</div>
  
  <div 
    class="modal fade" 
    id="install-app" 
    tabindex="-1" 
    role="dialog" 
  >
    <div class="modal-dialog">
      <div class="modal-content rounded-1">
        <div class="modal-body">
          <div class="d-flex flex-column align-items-center gap-2">
            <div class="error_icon">
              <i class="bx bxs-error"></i>
            </div>
            <div class="d-flex flex-column align-items-center">
              <h4 class="fw-bold fs-4">Install app?</h4>
              <p v-if="isIOS">To install the app, tap <strong>Share</strong> then <strong>Add to Home Screen</strong></p>
              <p v-else>Potato Pal Cool Store</p>
            </div>
          </div>
          <div class="d-flex justify-content-center pt-3 gap-3">
            <div>
              <button
                class="border-0 w-100 rounded-1 px-5 p-2 btn-secondary font-16"
                data-bs-dismiss="modal"
                v-text="isIOS ? `Close` : `Cancel`"
                @click="showInstallModal = false"
              />
            </div>
            <div v-if="!isIOS">
              <button
                class="border-0 w-100 rounded-1 px-5 p-2 btn-red"
                data-bs-dismiss="modal"
                @click="install"
                v-text="'Install'"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
