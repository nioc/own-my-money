<template>
  <transition :name="transitionName" mode="out-in" :enter-active-class="transitionEnterActiveClass">
    <slot/>
  </transition>
</template>

<script>
const DEFAULT_TRANSITION = 'fade'
export default {
  name: 'TransitionRouter',
  data () {
    return {
      transitionName: DEFAULT_TRANSITION,
      transitionEnterActiveClass: ''
    }
  },
  created () {
    this.$router.beforeEach((to, from, next) => {
      // get transition from meta
      let transitionName =
        to.meta.transitionName ||
        from.meta.transitionName ||
        DEFAULT_TRANSITION
      // get direction for slide
      if (transitionName === 'slide') {
        const toDepth = to.path.split('/').length
        const fromDepth = from.path.split('/').length
        transitionName = toDepth < fromDepth ? 'slide-right' : 'slide-left'
      }
      // set transition
      this.transitionEnterActiveClass = `${transitionName}-enter-active`
      this.transitionName = transitionName
      next()
    })
  }
}
</script>

<style lang="scss">
.fade-enter-active,
.fade-leave-active {
  transition-duration: 0.3s;
  transition-property: height, opacity;
  transition-timing-function: ease;
  overflow: hidden;
}

.fade-enter,
.fade-leave-active {
  opacity: 0;
}

.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition-duration: 0.5s;
  transition-property: height, opacity, transform;
  transition-timing-function: cubic-bezier(0.55, 0, 0.1, 1);
  overflow: hidden;
}

.slide-left-enter,
.slide-right-leave-active {
  opacity: 0;
  transform: translate(2em, 0);
}

.slide-left-leave-active,
.slide-right-enter {
  opacity: 0;
  transform: translate(-2em, 0);
}
</style>
