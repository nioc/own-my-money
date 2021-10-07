<template>
  <div v-if="isLoaded">
    <!-- dates filter -->
    <div class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <b-datepicker v-model="search.periodStart" placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" />
      </div>
      <div class="control">
        <b-datepicker v-model="search.periodEnd" placeholder="End date" icon="calendar" editable :max-date="search.currentDate" />
      </div>
      <div class="control">
        <button class="button" :class="{'is-loading': isLoading}" :disabled="isLoading" @click="requestData"><span class="icon"><i class="fa fa-refresh" /></span><span>{{ $t('actions.refresh') }}</span></button>
      </div>
    </div>
    <!-- dispatch table -->
    <b-table v-if="!isLoading" :data="accountDispatchs" :striped="true" :hoverable="true" default-sort-direction="desc" detailed custom-detail-row :mobile-cards="false">
      <!-- categories / users as main slot -->
      <b-table-column v-slot="props" field="id" :label="$tc('objects.category', 1)" sortable>
        <span v-if="props.row.id">{{ categoriesAndSubcategoriesLookup[props.row.id].label }}</span><span v-else>{{ $t('labels.uncategorizedTransaction') }}</span>
      </b-table-column>
      <b-table-column v-for="user in displayedUsers" :key="user" v-slot="props" :field="user.toString()" :label="getHolderName(user, $t('labels.NonAssigned'))" sortable numeric>
        <span :class="[getUserShare(user, props.row) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getUserShare(user, props.row), 'currency') }}</span>
      </b-table-column>
      <b-table-column v-slot="props" field="total" :label="$t('labels.total')" sortable numeric>
        <span :class="[getTotalShare(props.row) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getTotalShare(props.row), 'currency') }}</span>
      </b-table-column>
      <!-- subcategories as details slot -->
      <template #detail="props">
        <tr v-for="subcategory in props.row.subcategories" :key="subcategory.id">
          <td />
          <td class="px-5"><span v-if="subcategory.id">{{ categoriesAndSubcategoriesLookup[subcategory.id].label }}</span><span v-else>{{ $t('labels.uncategorizedTransaction') }}</span></td>
          <td v-for="user in displayedUsers" :key="user" class="has-text-right"><span :class="[getUserShare(user, subcategory) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getUserShare(user, subcategory), 'currency') }}</span></td>
          <td class="has-text-right"><span :class="[getTotalShare(subcategory) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getTotalShare(subcategory), 'currency') }}</span></td>
        </tr>
      </template>
      <!-- total by user as footer slot -->
      <template v-if="accountDispatchs.length" #footer>
        <th />
        <th class="is-hidden-mobile">
          <div class="th-wrap">Total</div>
        </th>
        <th v-for="user in displayedUsers" :key="user" class="is-hidden-mobile">
          <div class="th-wrap is-numeric" :class="[getUserSum(user) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getUserSum(user), 'currency') }}</div>
        </th>
        <th class="is-hidden-mobile">
          <div class="th-wrap is-numeric" :class="[getTotalSum() < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getTotalSum(), 'currency') }}</div>
        </th>
      </template>
      <!-- no data -->
      <template #empty>
        <section class="section">
          <div class="content has-text-grey has-text-centered">
            <p>{{ $t('labels.nothingToDisplay') }}</p>
          </div>
        </section>
      </template>
    </b-table>
  </div>
</template>

<script>
import Config from '@/services/Config'
import CategoriesFactory from '@/services/Categories'
import HoldersFactory from '@/services/Holders'
export default {
  components: {
  },
  mixins: [CategoriesFactory, HoldersFactory],
  props: {
    id: {
      type: Number,
      required: true,
    },
    duration: {
      type: String,
      required: true,
    },
  },
  data () {
    const today = new Date()
    today.setHours(0, 0, 0)
    today.setMilliseconds(0)
    return {
      isLoading: false,
      isLoaded: false,
      search: {
        currentDate: today,
        periodStart: this.$moment(today).subtract(this.$moment.duration(this.duration)).toDate(),
        periodEnd: today,
      },
      accountDispatchs: [],
      displayedUsers: [],
      error: '',
      // resources
      rAccountCategories: this.$resource(Config.API_URL + 'accounts{/id}/categories'),
    }
  },
  mounted () {
    this.getCategories()
    this.getHolders()
      .then(() => this.requestData())
  },
  methods: {
    requestData () {
      this.isLoading = true
      const params = {
        id: this.id,
        periodStart: this.$moment(this.search.periodStart).format('X'),
        periodEnd: this.$moment(this.search.periodEnd).format('X'),
      }
      this.rAccountCategories.query(params)
        .then((response) => {
          // set users
          const displayedUsers = []
          response.body.forEach((category) => {
            category.shares.forEach((share) => {
              displayedUsers.push(share.userId)
            })
          })
          this.displayedUsers = [...new Set(displayedUsers.sort())]
          // parsing function for categoies and subcategories
          function parseCategory (rawCollection) {
            const collection = []
            rawCollection.forEach((rawItem) => {
              // create category
              const item = {
                id: rawItem.id,
              }
              // set users shares
              displayedUsers.forEach(user => {
                item[user] = 0
              })
              rawItem.shares.forEach((share) => {
                item[share.userId] = share.share
              })
              // set subcategories
              if (Object.prototype.hasOwnProperty.call(rawItem, 'subcategories')) {
                item.subcategories = parseCategory(rawItem.subcategories)
              }
              collection.push(item)
            })
            return collection
          }
          // set categories
          this.accountDispatchs = parseCategory(response.body)
        }, (response) => {
          if (response.status === 403 || response.status === 404) {
            // user can not access this account, return to home
            this.$router.replace({ name: 'home' })
            return
          }
          // @TODO : add error handling
          this.error = response.status + ' - ' + response.statusText
          if (response.body.message) {
            this.error = response.body.message
          }
        })
        .finally(function () {
          // remove loading overlay when API replies
          this.isLoading = false
          this.isLoaded = true
        })
    },
    getUserShare (userId, shares) {
      return (shares[userId]) ? shares[userId] : 0
    },
    getUserSum (userId) {
      return this.accountDispatchs
        .reduce((sum, item) => (item[userId]) ? sum + item[userId] : sum, 0)
    },
    getTotalShare (item) {
      let sum = 0
      for (const key in item) {
        if (key !== 'id' && key !== 'subcategories' && isFinite(item[key])) {
          sum += item[key]
        }
      }
      return sum
    },
    getTotalSum () {
      return this.accountDispatchs
        .reduce((sum, item) => {
          for (const key in item) {
            if (key !== 'id' && key !== 'subcategories' && isFinite(item[key])) {
              sum += item[key]
            }
          }
          return sum
        }, 0)
    },
  },
}
</script>
