<template>
  <div v-if="isLoaded">
    <!-- dates filter -->
    <div class="field is-grouped is-grouped-multiline is-block-mobile">
      <div class="control">
        <o-datepicker v-model="search.periodStart" placeholder="Start date" icon="calendar" editable :max-date="search.currentDate" lists-class="field has-addons" />
      </div>
      <div class="control">
        <o-datepicker v-model="search.periodEnd" placeholder="End date" icon="calendar" editable :max-date="search.currentDate" lists-class="field has-addons" />
      </div>
      <div class="control">
        <button class="button" :class="{'is-loading': isLoading}" :disabled="isLoading" @click="requestData"><span class="icon"><i class="fas fa-sync-alt" /></span><span>{{ $t('actions.refresh') }}</span></button>
      </div>
    </div>
    <!-- dispatch table -->
    <o-table v-if="!isLoading" :data="accountDispatchs" :striped="true" :hoverable="true" default-sort-direction="desc" detailed custom-detail-row :mobile-cards="false">
      <!-- categories / users as main slot -->
      <o-table-column v-slot="props" field="id" :label="$tc('objects.category', 1)" sortable>
        <span v-if="props.row.id">{{ categoriesAndSubcategoriesLookup[props.row.id].label }}</span><span v-else>{{ $t('labels.uncategorizedTransaction') }}</span>
      </o-table-column>
      <o-table-column v-for="user in displayedUsers" :key="user" v-slot="props" :field="user.toString()" :label="getHolderName(user, $t('labels.NonAssigned'))" sortable numeric position="right">
        <span :class="[getUserShare(user, props.row) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getUserShare(user, props.row), 'currency') }}</span>
      </o-table-column>
      <o-table-column v-slot="props" field="total" :label="$t('labels.total')" sortable numeric position="right">
        <span :class="[getTotalShare(props.row) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getTotalShare(props.row), 'currency') }}</span>
      </o-table-column>
      <!-- subcategories as details slot -->
      <template #detail="props">
        <tr v-for="subcategory in props.row.subcategories" :key="subcategory.id" class="is-size-7 has-text-weight-light">
          <td />
          <td class="px-5"><span v-if="subcategory.id">{{ categoriesAndSubcategoriesLookup[subcategory.id].label }}</span><span v-else>{{ $t('labels.uncategorizedTransaction') }}</span></td>
          <td v-for="user in displayedUsers" :key="user" class="has-text-right"><span :class="[getUserShare(user, subcategory) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getUserShare(user, subcategory), 'currency') }}</span></td>
          <td class="has-text-right"><span :class="[getTotalShare(subcategory) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getTotalShare(subcategory), 'currency') }}</span></td>
        </tr>
      </template>
      <!-- total by user as footer slot -->
      <template v-if="accountDispatchs.length" #footer>
        <th />
        <th>
          <div>Total</div>
        </th>
        <th v-for="user in displayedUsers" :key="user" class="has-text-right">
          <div :class="[getUserSum(user) < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getUserSum(user), 'currency') }}</div>
        </th>
        <th class="has-text-right">
          <div :class="[getTotalSum() < 0 ? 'has-text-danger' : 'has-text-primary']">{{ $n(getTotalSum(), 'currency') }}</div>
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
    </o-table>
  </div>
</template>

<script>
import { useStore } from '@/store'
import { mapState } from 'pinia'

// parsing function for categoies and subcategories
function parseCategory (holders, rawCollection) {
  const collection = []
  rawCollection.forEach((rawItem) => {
    // create category
    const item = {
      id: rawItem.id,
    }
    // set users shares
    holders.forEach(holder => {
      item[holder] = 0
    })
    rawItem.shares.forEach((share) => {
      item[share.userId] = share.share
    })
    // set subcategories
    if (Object.prototype.hasOwnProperty.call(rawItem, 'subcategories')) {
      item.subcategories = parseCategory(holders, rawItem.subcategories)
    }
    collection.push(item)
  })
  return collection
}

export default {
  components: {
  },
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
        periodStart: this.$dayjs(today).subtract(this.$dayjs.duration(this.duration)).toDate(),
        periodEnd: today,
      },
      accountDispatchs: [],
      displayedUsers: [],
      error: '',
    }
  },
  computed: {
    ...mapState(useStore, ['categories', 'categoriesAndSubcategoriesLookup', 'holders', 'getHolderName']),
  },
  mounted () {
    this.requestData()
  },
  methods: {
    async requestData () {
      this.isLoading = true
      const params = {
        id: this.id,
        periodStart: this.$dayjs(this.search.periodStart).format('X'),
        periodEnd: this.$dayjs(this.search.periodEnd).format('X'),
      }
      try {
        const response = await this.$http.get(`accounts/${this.id}/categories`, params)
        // set users
        const displayedUsers = []
        response.data.forEach((category) => {
          category.shares.forEach((share) => {
            displayedUsers.push(share.userId)
          })
        })
        this.displayedUsers = [...new Set(displayedUsers.sort())]
        // set categories
        this.accountDispatchs = parseCategory(displayedUsers, response.data)
      } catch (error) {
        if (error.response.status === 403 || error.response.status === 404) {
          // user can not access this account, return to home
          this.$router.replace({ name: 'home' })
          return
        }
        // @TODO : add error handling
        this.error = error.message
      }
      // remove loading overlay when API replies
      this.isLoading = false
      this.isLoaded = true
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
