<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fas fa-home', text: $t('labels.home')},
          {link: '/users', icon: 'fas fa-users', text: $tc('objects.user', 2), isActive: true},
        ]"
      />
    </div>

    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.user', 2) }}</h1>

        <o-table :data="displayedUsers" :striped="true" :hoverable="true" root-class="table-container" td-class="is-clickable" @select="edit">
          <o-table-column v-slot="props" :label="$t('fieldnames.login')">
            {{ props.row.login }}
          </o-table-column>
          <o-table-column v-slot="props" :label="$t('fieldnames.email')">
            {{ props.row.mail }}
          </o-table-column>
          <o-table-column v-slot="props" :label="$t('fieldnames.admin')">
            <o-switch v-model="props.row.isAdmin" disabled />
          </o-table-column>
          <o-table-column v-slot="props" :label="$t('fieldnames.status')">
            <o-switch v-model="props.row.status" disabled />
          </o-table-column>
        </o-table>

        <div class="field is-grouped">
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="create"><span class="icon"><i class="fas fa-user-plus" /></span><span>{{ $t('actions.addUser') }}</span></button>
          </p>
        </div>
        <div v-if="error" class="message is-danger">
          <div class="message-body">
            {{ error }}
          </div>
        </div>

        <o-loading :active="isLoading" :full-page="false" />

      </div>
    </div>
  </section>
</template>

<script>
import Breadcrumb from '@/components/Breadcrumb.vue'
import UserForm from '@/components/UserForm.vue'

export default {
  name: 'Users',
  components: {
    Breadcrumb,
  },
  data () {
    return {
      users: [],
      error: '',
      isLoading: false,
    }
  },
  computed: {
    isOnline () {
      return this.$store.isOnline
    },
    displayedUsers () {
      // return isAdmin boolean from user scope
      return this.users.slice().map((user) => {
        user.isAdmin = user.scope.includes('admin')
        return user
      })
    },
  },
  mounted () {
    this.get()
  },
  methods: {
    async get () {
      this.isLoading = true
      try {
        const response = await this.$http.get('users')
        this.users = response.data
      } catch (error) {
        this.error = error.message
      }
      this.isLoading = false
    },
    create () {
      // open modal user form in creation mode
      this.$oruga.modal.open({
        component: UserForm,
        trapFocus: true,
        props: {
          user: { scope: 'user', status: true },
        },
        onClose: () => this.get(),
      })
    },
    edit (user) {
      // open modal user form in edition mode with requested user
      const modalUser = JSON.parse(JSON.stringify(user))
      delete modalUser.isAdmin
      this.$oruga.modal.open({
        component: UserForm,
        trapFocus: true,
        props: {
          user: modalUser,
        },
        onClose: () => this.get(),
      })
    },
  },
}
</script>
