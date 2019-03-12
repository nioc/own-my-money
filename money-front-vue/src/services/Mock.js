import Config from './../services/Config'

export default {
  install (Vue, options) {
    function returnDateFromNow (duration) {
      return Vue.moment().subtract(Vue.moment.duration(duration)).format()
    }
    let transactions = [
      { id: 1, aid: 1, type: 'CREDIT', datePosted: returnDateFromNow('P3D'), dateUser: returnDateFromNow('P3D'), amount: 342, name: 'SUNSHINE AUTOS IMPORT GARAGE', fitid: '5387240011638', memo: '', category: 3, subcategory: 33, note: 'Car selling' },
      { id: 10, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P4D'), dateUser: returnDateFromNow('P4D'), amount: -60.5, name: 'ARROW GASOLINE', fitid: '5387600307180', memo: '', category: 1, subcategory: 12, note: 'Gasoil' },
      { id: 2, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P4D'), dateUser: returnDateFromNow('P4D'), amount: -39.85, name: 'LOS SANTOS CUSTOMS', fitid: '5387600307178', memo: '', category: 1, subcategory: 14, note: 'Wheels' },
      { id: 3, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P5D'), dateUser: returnDateFromNow('P5D'), amount: -17, name: 'PIZZA THIS', fitid: '5387600307179', memo: '', category: 2, subcategory: 20, note: 'Lunch with Trevor' },
      { id: 5, aid: 1, type: 'CREDIT', datePosted: returnDateFromNow('P6D'), dateUser: returnDateFromNow('P6D'), amount: 300, name: 'MERRYWEATHER SECURITY', fitid: '5387600307179', memo: '', category: 3, subcategory: 32, note: '' },
      { id: 9, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P6D'), dateUser: returnDateFromNow('P6D'), amount: -39.99, name: 'MORS MUTUAL INSURANCE', fitid: '5387600307180', memo: '', category: 1, subcategory: 13, note: '' },
      { id: 6, aid: 1, type: 'CREDIT', datePosted: returnDateFromNow('P8D'), dateUser: returnDateFromNow('P8D'), amount: 200, name: 'TRANSFER FROM L. CREST', fitid: '5387600307180', memo: '', category: 3, subcategory: 32, note: 'Mission success' },
      { id: 8, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P8D'), dateUser: returnDateFromNow('P8D'), amount: -99, name: 'VANILLA UNICORN', fitid: '5387600307179', memo: '', category: 2, subcategory: null, note: '' },
      { id: 4, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P15D'), dateUser: returnDateFromNow('P15D'), amount: -28.5, name: 'PONSONBY', fitid: '5387600307180', memo: '', category: 5, subcategory: 51, note: 'hats' },
      { id: 9, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P18D'), dateUser: returnDateFromNow('P18D'), amount: -60.5, name: 'ARROW GASOLINE', fitid: '5387600307180', memo: '', category: 1, subcategory: 12, note: 'Gasoil' },
      { id: 7, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P20D'), dateUser: returnDateFromNow('P20D'), amount: -250, name: 'PEGASUS', fitid: '5387600307178', memo: '', category: 1, subcategory: 14, note: '' },
      { id: 9, aid: 1, type: 'DEBIT', datePosted: returnDateFromNow('P21D'), dateUser: returnDateFromNow('P21D'), amount: -60.5, name: 'O SHEAS BARBERS SHOP', fitid: '5387600307181', memo: '', category: 5, subcategory: null, note: '' },
      { id: 9, aid: 1, type: 'CREDIT', datePosted: returnDateFromNow('P22D'), dateUser: returnDateFromNow('P22D'), amount: 210, name: 'SUNSHINE AUTOS IMPORT GARAGE', fitid: '5387600307180', memo: '', category: 3, subcategory: 33, note: 'Car selling' },
      { id: 9, aid: 2, type: 'CREDIT', datePosted: returnDateFromNow('P21D'), dateUser: returnDateFromNow('P21D'), amount: 100, name: 'DEPOSIT', fitid: '5387600307190', memo: '', category: 3, subcategory: 32, note: '' },
      { id: 9, aid: 2, type: 'DEBIT', datePosted: returnDateFromNow('P16D'), dateUser: returnDateFromNow('P16D'), amount: -19.99, name: 'WITHDRAW', fitid: '5387600307191', memo: '', category: 2, subcategory: 21, note: '' }
    ]
    let accounts = [
      { 'id': 1, user: '1', bankId: '14000', branchId: '00170', accountId: '02437797004', label: 'Maze Bank', duration: 'P3M', iconUrl: './img/accounts-1-icons.png', balance: 1635, lastUpdate: returnDateFromNow('PT4H') },
      { 'id': 2, user: '1', bankId: '17000', branchId: '00360', accountId: '07894688002', label: 'Fleeca', duration: 'P3M', iconUrl: './img/accounts-2-icons.png', balance: 201, lastUpdate: returnDateFromNow('P3D') }
    ]
    // let history = []
    function groupBy (collection, attribute) {
      return collection.reduce(function (acc, obj) {
        var key = obj[attribute]
        if (!acc[key]) {
          acc[key] = []
        }
        acc[key].push(obj)
        return acc
      }, {})
    }
    let datesByCategory = []

    function getHistory (collection, balance) {
      let dateValues = []
      let transactionsByDate = groupBy(collection, 'datePosted')
      for (let date in transactionsByDate) {
        let dateByType = groupBy(transactionsByDate[date], 'type')
        let debit = 'DEBIT' in dateByType ? dateByType['DEBIT'].reduce((a, b) => a + b.amount, 0) : 0
        let credit = 'CREDIT' in dateByType ? dateByType['CREDIT'].reduce((a, b) => a + b.amount, 0) : 0
        dateValues.push({
          date: Vue.moment(date).format('YYYY-MM-DD'),
          debit: debit,
          credit: credit
        })
      }
      let startDate = Vue.moment().subtract(Vue.moment.duration('P1M'))
      let endDate = Vue.moment()
      var dt = startDate
      let length = dateValues.length
      // eslint-disable-next-line
      while (dt <= endDate) {
        let dateExists = false
        for (var i = 0; i < length; i++) {
          if (dateValues[i].date === dt.format('YYYY-MM-DD')) {
            dateExists = true
            break
          }
        }
        if (!dateExists) {
          dateValues.push({
            date: Vue.moment(dt).format('YYYY-MM-DD'),
            debit: 0,
            credit: 0
          })
        }
        dt.add(1, 'days')
      }
      // set balance
      dateValues.sort(function (a, b) {
        return Vue.moment(b.date) - Vue.moment(a.date)
      })
      for (let date in dateValues) {
        let currentDate = dateValues[date]
        currentDate.balance = balance
        balance -= currentDate.credit - currentDate.debit
      }
      // reorder values
      dateValues.sort(function (a, b) {
        return Vue.moment(a.date) - Vue.moment(b.date)
      })
      return dateValues
    }
    let transactionsByType = groupBy(transactions, 'type')
    let debits = groupBy(transactionsByType['DEBIT'], 'category')
    let credits = groupBy(transactionsByType['CREDIT'], 'category')
    let debitsDistribution = []
    let creditsDistribution = []
    let debitsDistributionByCategory = []
    let creditsDistributionByCategory = []
    let transactionsByCategory = groupBy(transactions, 'category')
    for (var i = 1; i < 6; i++) {
      debitsDistributionByCategory[i] = []
      creditsDistributionByCategory[i] = []
      datesByCategory[i] = []
      if (transactionsByCategory[i]) {
        datesByCategory[i] = getHistory(transactionsByCategory[i])
      }
    }
    // handle debits category distribution
    for (let debit in debits) {
      debitsDistribution.push({ key: debit, amount: Math.abs(debits[debit].reduce((a, b) => a + b.amount, 0)) })

      let debitsForCategory = groupBy(debits[debit], 'subcategory')
      for (let debitForCategory in debitsForCategory) {
        debitsDistributionByCategory[debit].push({ key: debitForCategory, amount: Math.abs(debitsForCategory[debitForCategory].reduce((a, b) => a + b.amount, 0)) })
      }
    }
    // handle credits category distribution
    for (let credit in credits) {
      creditsDistribution.push({ key: credit, amount: Math.abs(credits[credit].reduce((a, b) => a + b.amount, 0)) })
      let creditsForCategory = groupBy(credits[credit], 'subcategory')
      for (let creditForCategory in creditsForCategory) {
        creditsDistributionByCategory[credit].push({ key: creditForCategory, amount: Math.abs(creditsForCategory[creditForCategory].reduce((a, b) => a + b.amount, 0)) })
      }
    }
    let routes = [
      // login
      {
        method: 'POST',
        url: 'users/tokens',
        response: { token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJtb25leSIsImV4cCI6MTc2MzEwNDEwMCwic3ViIjoxLCJsb2dpbiI6Ik1pY2hhZWwiLCJzdGF0dXMiOnRydWUsIm1haWwiOiJtQGV5ZWZpbmQuaW5mbyIsInNjb3BlIjoidXNlciBhZG1pbiIsImxhbmd1YWdlIjoiZW4ifQ.vAPx5Bd2bduUFF6s4gplFwQKCDgCAEjJHTU4qnltUkw' }
      },
      // users management
      {
        method: 'GET',
        url: 'users',
        response: [
          { 'sub': 1, login: 'Michael', status: true, mail: 'm@eyefind.info', scope: 'user admin' },
          { 'sub': 2, login: 'Trevor', status: false, mail: 'tpi@eyefind.info', scope: 'user' },
          { 'sub': 3, login: 'Franklin', status: true, mail: 'frankie@eyefind.info', scope: 'user' }
        ]
      },
      {
        method: 'PUT',
        url: 'users/1',
        response: { sub: 1, login: 'Michael', 'status': true, mail: 'm@eyefind.info', scope: 'user admin' }
      },
      {
        method: 'PUT',
        url: 'users/2',
        response: { sub: 1, login: 'Trevor', 'status': false, mail: 'tpi@eyefind.info', scope: 'user' }
      },
      {
        method: 'PUT',
        url: 'users/3',
        response: { sub: 1, login: 'Franklin', 'status': true, mail: 'frankie@eyefind.info', scope: 'user' }
      },
      {
        method: 'GET',
        url: 'users/1/tokens',
        response: [
          { creation: returnDateFromNow('P1D'), ip: '10.0.0.2', userAgent: 'Mozilla/5.0 (X11; Linux x86_64; rv:63.0) Gecko/20100101 Firefox/63.0' },
          { creation: returnDateFromNow('PT41242M'), ip: '10.0.0.13', userAgent: 'Mozilla/5.0 (Android 8.0.0; Mobile; rv:63.0) Gecko/63.0 Firefox/63.0' },
          { creation: returnDateFromNow('PT1044887S'), ip: '10.0.0.14', userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53' }
        ]
      },
      // home
      {
        method: 'GET',
        url: 'transactions',
        response: transactions.map((transaction) => {
          transaction.iconUrl = 'accounts-' + transaction.aid + '-icons.png'
          transaction.accountLabel = accounts[transaction.aid - 1].label
          return transaction
        })
      },
      {
        method: 'GET',
        url: 'transactions/history',
        response: { values: getHistory(transactions, accounts.reduce((sum, account) => sum + account.balance, 0)), periodStart: returnDateFromNow('P1M'), periodEnd: returnDateFromNow('PT1S') }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/credit/categories',
        response: { values: creditsDistribution, periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'credit', key: 'categories' }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/debit/categories',
        response: { values: debitsDistribution, periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'debit', key: 'categories' }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/credit/subcategories?value=1',
        response: { values: creditsDistributionByCategory[1], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'credit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/debit/subcategories?value=1',
        response: { values: debitsDistributionByCategory[1], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'debit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/history?category=1',
        response: { values: datesByCategory[1], periodStart: returnDateFromNow('P1M'), periodEnd: returnDateFromNow('PT1S') }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/credit/subcategories?value=2',
        response: { values: creditsDistributionByCategory[2], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'credit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/debit/subcategories?value=2',
        response: { values: debitsDistributionByCategory[2], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'debit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/history?category=2',
        response: { values: datesByCategory[2], periodStart: returnDateFromNow('P1M'), periodEnd: returnDateFromNow('PT1S') }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/credit/subcategories?value=3',
        response: { values: creditsDistributionByCategory[3], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'credit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/debit/subcategories?value=3',
        response: { values: debitsDistributionByCategory[3], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'debit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/history?category=3',
        response: { values: datesByCategory[3], periodStart: returnDateFromNow('P1M'), periodEnd: returnDateFromNow('PT1S') }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/credit/subcategories?value=5',
        response: { values: creditsDistributionByCategory[5], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'credit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/debit/subcategories?value=5',
        response: { values: debitsDistributionByCategory[5], periodStart: Vue.moment().subtract(Vue.moment.duration('P1M')), periodEnd: Vue.moment(), type: 'debit', key: 'subcategories' }
      },
      {
        method: 'GET',
        url: 'transactions/history?category=5',
        response: { values: datesByCategory[5], periodStart: returnDateFromNow('P1M'), periodEnd: returnDateFromNow('PT1S') }
      },
      // accounts
      {
        method: 'GET',
        url: 'accounts',
        response: accounts
      },
      // account 1
      {
        method: 'GET',
        url: 'accounts/1',
        response: accounts[0]
      },
      // account 2
      {
        method: 'GET',
        url: 'accounts/2',
        response: accounts[1]
      },
      // account 1 > transactions
      {
        method: 'GET',
        url: 'accounts/1/transactions',
        response: transactions.filter((transaction) => transaction.aid === 1).map((transaction) => {
          transaction.iconUrl = './img/accounts-1-icons.png'
          return transaction
        })
      },
      {
        method: 'GET',
        url: 'accounts/1/transactions/history',
        response: { values: getHistory(transactions.filter((transaction) => transaction.aid === 1), accounts[0].balance), periodStart: returnDateFromNow('P1M'), periodEnd: returnDateFromNow('PT1S') }
      },
      // account 2 > transactions
      {
        method: 'GET',
        url: 'accounts/2/transactions',
        response: transactions.filter((transaction) => transaction.aid === 2).map((transaction) => {
          transaction.iconUrl = './img/accounts-2-icons.png'
          return transaction
        })
      },
      {
        method: 'GET',
        url: 'accounts/2/transactions/history',
        response: { values: getHistory(transactions.filter((transaction) => transaction.aid === 2), accounts[1].balance), periodStart: returnDateFromNow('P1M'), periodEnd: returnDateFromNow('PT1S') }
      },
      // about
      {
        method: 'GET',
        url: 'setup/versions/latest',
        response: { installed: '0.8.0', latest: '0.8.0' }
      },
      // categories
      {
        method: 'GET',
        url: 'categories',
        response: [
          { id: 1, label: 'Transports', status: true, icon: 'fa-train', 'isBudgeted': true, sub: [ { id: 11, label: 'Public transports', status: true, icon: null, parentId: 1 }, { id: 12, label: 'Fuels', status: true, icon: null, parentId: 1 }, { id: 13, label: 'Insurance', status: true, icon: null, parentId: 1 }, { id: 14, label: 'Maintenance', status: true, icon: null, parentId: 1 } ] },
          { id: 2, label: 'Hobbies', status: true, icon: 'fa-beer', 'isBudgeted': true, sub: [ { id: 20, label: 'Restaurant', status: true, icon: null, parentId: 2 }, { id: 21, label: 'Drink', status: true, icon: null, parentId: 2 } ] },
          { id: 3, label: 'Income', status: true, icon: 'fa-briefcase', 'isBudgeted': true, sub: [ { id: 31, label: 'Wages', status: true, icon: null, parentId: 3 }, { id: 32, label: 'Reward', status: true, icon: null, parentId: 3 }, { id: 33, label: 'Sells', status: true, icon: null, parentId: 3 }, { id: 34, label: 'Gifts', status: false, icon: null, parentId: 3 } ] },
          { id: 4, label: 'Home', status: true, icon: 'fa-home', 'isBudgeted': true, sub: [ { id: 41, label: 'Insurance', status: true, icon: null, parentId: 4 }, { id: 42, label: 'Rent', status: true, icon: null, parentId: 4 }, { id: 43, label: 'Mortgage', status: false, icon: null, parentId: 4 }, { id: 44, label: 'Furnishing', status: true, icon: null, parentId: 4 }, { id: 45, label: 'Electricity', status: true, icon: null, parentId: 4 }, { id: 46, label: 'Water', status: true, icon: null, parentId: 4 }, { id: 47, label: 'Internet', status: true, icon: null, parentId: 4 } ] },
          { id: 5, label: 'Clothing', status: true, icon: 'fa-user-secret', 'isBudgeted': true, sub: [ { id: 51, label: 'Clothes', status: true, icon: null, parentId: 5 }, { id: 52, label: 'Shoes', status: true, icon: null, parentId: 5 } ] }
        ]
      },
      // patterns
      {
        method: 'GET',
        url: 'patterns',
        response: [
          { id: 1, label: 'MORS MUTUAL INSURANCE', category: 1, subcategory: 13 },
          { id: 2, label: 'LOS SANTOS CUSTOMS*', category: 1, subcategory: 13 },
          { id: 2, label: 'PONSONBY*', category: 5, subcategory: 51 }
        ]
      }
    ]

    Vue.http.interceptors.push((request) => {
      let url = request.getUrl()
      let length = routes.length
      let routeExists = false
      let route = {}
      for (var i = 0; i < length; i++) {
        if (request.method === routes[i].method && url === Config.API_URL + routes[i].url) {
          routeExists = true
          route = routes[i]
          break
        }
      }
      console.debug(url, routeExists)
      if (!routeExists) {
        // console.log(url)
        return request.respondWith({ status: 404, statusText: 'Not found' })
      }
      return request.respondWith(
        route.response,
        { status: 200 }
      )
    })
  }
}
