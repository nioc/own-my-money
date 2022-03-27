<template>
  <o-modal v-model:active="needRefresh" content-class="modal-content" :can-cancel="false" :trap-focus="true">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ $t('labels.wpaUpdateTitle') }}</p>
      </header>
      <section class="modal-card-body">
        <p>{{ $t('labels.wpaUpdateMessage') }}</p>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-primary" autofocus @click="udpateApp"><span class="icon"><i class="fas fa-sync-alt" /></span><span>{{ $t('actions.refresh') }}</span></button>
      </footer>
    </div>
  </o-modal>
</template>

<script>
import { useRegisterSW } from "virtual:pwa-register/vue"
const { updateServiceWorker } = useRegisterSW()

export default {
  name: "UpdatePwa",
  setup() {
    const { needRefresh, updateServiceWorker } = useRegisterSW()
    return { needRefresh, updateServiceWorker }
  },
  methods: {
    async udpateApp() {
      await updateServiceWorker()
    },
  },
}
</script>