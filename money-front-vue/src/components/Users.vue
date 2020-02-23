<template>
  <section class="hero">
    <div class="hero-head">
      <breadcrumb
        :items="[
          {link: '/', icon: 'fa-home', text: this.$t('labels.home')},
          {link: '/users', icon: 'fa-users', text: this.$tc('objects.user', 2), isActive: true},
        ]"
      />
    </div>

    <div class="hero-body">
      <div class="container box">
        <h1 class="title container">{{ $tc('objects.user', 2) }}</h1>

        <b-table :data="displayedUsers" :striped="true" :hoverable="true" class="table-container" @select="edit">
          <template slot-scope="props">
            <b-table-column :label="$t('fieldnames.login')">
              {{ props.row.login }}
            </b-table-column>
            <b-table-column :label="$t('fieldnames.email')">
              {{ props.row.mail }}
            </b-table-column>
            <b-table-column :label="$t('fieldnames.admin')">
              <b-switch v-model="props.row.isAdmin" disabled />
            </b-table-column>
            <b-table-column :label="$t('fieldnames.status')">
              <b-switch v-model="props.row.status" disabled />
            </b-table-column>
          </template>
        </b-table>

        <div class="field is-grouped">
          <p class="control">
            <button class="button is-primary" role="button" :disabled="!isOnline" @click="create"><i class="fa fa-user-plus fa-mr" />{{ $t('actions.addUser') }}</button>
          </p>
        </div>
        <div v-if="error" class="message is-danger">
          <div class="message-body">
            {{ error }}
          </div>
        </div>

        <b-modal :active.sync="modalUser.isActive" has-modal-card scroll="keep" @close="get">
          <user-form v-bind="modalUser" />
        </b-modal>

        <b-loading :is-full-page="false" :active.sync="isLoading" />

      </div>
    </div>
  </section>
</template>

<script>
import Config from './../services/Config'
import Breadcrumb from '@/components/Breadcrumb'
import UserForm from '@/components/UserForm'
export default {
  name: 'Users',
  components: {
    Breadcrumb,
    UserForm,
  },
  data () {
    return {
      users: [],
      error: '',
      isLoading: false,
      // modal
      modalUser: {
        isActive: false,
        user: {},
      },
      // resources
      rUsers: this.$resource(Config.API_URL + 'users{/id}'),
    }
  },
  computed: {
    isOnline () {
      return this.$store.state.isOnline
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
    get () {
      this.isLoading = true
      this.rUsers.query()
        .then((response) => {
          this.users = response.body
        }, (response) => {
          if (response.body.message) {
            this.error = response.body.message
            return
          }
          this.error = response.status + ' - ' + response.statusText
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.isLoading = false
        })
    },
    create () {
      // open modal user form in creation mode
      this.modalUser.user = { scope: 'user', status: true }
      this.modalUser.isActive = true
    },
    edit (user) {
      // open modal user form in edition mode with requested user
      this.modalUser.user = JSON.parse(JSON.stringify(user))
      delete this.modalUser.user.isAdmin
      this.modalUser.isActive = true
    },
  },
}
</script>
