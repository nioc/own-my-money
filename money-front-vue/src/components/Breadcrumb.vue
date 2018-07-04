<template>
  <div class="box is-radiusless">
    <nav class="breadcrumb container" aria-label="breadcrumbs">
      <ul>
        <li v-for="item in items" :key="item.text" :class="{ 'is-active': item.isActive }">
          <router-link :to="item.link">
            <span v-if="item.icon" class="icon is-small">
              <i class="fa" :class="item.icon" aria-hidden="true"></i>
            </span>
            <span>{{item.text}}</span>
          </router-link>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
export default {
  props: {
    items: {
      type: Array,
      required: true
    }
  }
}
</script>
