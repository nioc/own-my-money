<template>
  <div class="box is-radiusless has-background-white-bis">
    <nav class="breadcrumb container" aria-label="breadcrumbs">
      <ul>
        <li v-for="item in items" :key="item.text" :class="{'is-active': item.isActive}">
          <router-link :to="item.link">
            <span v-if="item.icon" class="icon is-small">
              <i :class="item.icon" aria-hidden="true" />
            </span>
            <span :class="{'is-hidden-mobile': item.icon}">{{ item.text }}</span>
          </router-link>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
export default {
  name: 'Breadcrumb',
  props: {
    items: {
      type: Array,
      required: true,
    },
  },
}
</script>
<style>
.breadcrumb ul {
  flex-wrap: nowrap;
  overflow: auto;
}
.breadcrumb li:first-child::before {
  content: "\00a0";
}
@media screen and (max-width: 768px) {
  .breadcrumb .icon:first-child {
    margin-right: initial;
  }
}
</style>
