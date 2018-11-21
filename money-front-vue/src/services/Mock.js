import Config from './../services/Config'

export default {
  install (Vue, options) {
    let transactions = [
      {id: 1, type: 'CREDIT', datePosted: Vue.moment().subtract(1, 'days'), dateUser: Vue.moment().subtract(1, 'days'), amount: 543, name: 'SUNSHINE AUTOS IMPORT GARAGE', fitid: '5387240011638', memo: '', category: 3, subcategory: 33, note: 'Car selling'},
      {id: 2, type: 'DEBIT', datePosted: Vue.moment().subtract(3, 'days'), dateUser: Vue.moment().subtract(3, 'days'), amount: -39.85, name: 'LOS SANTOS CUSTOMS', fitid: '5387600307178', memo: '', category: 1, subcategory: 14, note: 'Wheels'},
      {id: 7, type: 'DEBIT', datePosted: Vue.moment().subtract(3, 'days'), dateUser: Vue.moment().subtract(3, 'days'), amount: -250, name: 'PEGASUS', fitid: '5387600307178', memo: '', category: 1, subcategory: 14, note: ''},
      {id: 3, type: 'DEBIT', datePosted: Vue.moment().subtract(5, 'days'), dateUser: Vue.moment().subtract(5, 'days'), amount: -17, name: 'PIZZA THIS', fitid: '5387600307179', memo: '', category: 2, subcategory: 20, note: 'Lunch with Trevor'},
      {id: 5, type: 'CREDIT', datePosted: Vue.moment().subtract(6, 'days'), dateUser: Vue.moment().subtract(6, 'days'), amount: 300, name: 'MERRYWEATHER SECURITY', fitid: '5387600307179', memo: '', category: 3, subcategory: 32, note: ''},
      {id: 9, type: 'DEBIT', datePosted: Vue.moment().subtract(6, 'days'), dateUser: Vue.moment().subtract(6, 'days'), amount: -39.99, name: 'MORS MUTUAL INSURANCE', fitid: '5387600307180', memo: '', category: 1, subcategory: 13, note: ''},
      {id: 6, type: 'CREDIT', datePosted: Vue.moment().subtract(8, 'days'), dateUser: Vue.moment().subtract(8, 'days'), amount: 200, name: 'TRANSFER FROM L. CREST', fitid: '5387600307180', memo: '', category: 3, subcategory: 32, note: 'Mission success'},
      {id: 8, type: 'DEBIT', datePosted: Vue.moment().subtract(8, 'days'), dateUser: Vue.moment().subtract(8, 'days'), amount: -59, name: 'VANILLA UNICORN', fitid: '5387600307179', memo: '', category: 2, subcategory: null, note: ''},
      {id: 4, type: 'DEBIT', datePosted: Vue.moment().subtract(15, 'days'), dateUser: Vue.moment().subtract(15, 'days'), amount: -28.5, name: 'PONSONBY', fitid: '5387600307180', memo: '', category: 5, subcategory: 51, note: 'hats'},
      {id: 9, type: 'DEBIT', datePosted: Vue.moment().subtract(18, 'days'), dateUser: Vue.moment().subtract(18, 'days'), amount: -60.5, name: 'ARROW GASOLINE', fitid: '5387600307180', memo: '', category: 1, subcategory: 12, note: 'Gasoil'}
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
    let dates = []
    let transactionsByDate = groupBy(transactions, 'datePosted')
    for (let date in transactionsByDate) {
      let dateByType = groupBy(transactionsByDate[date], 'type')
      dates.push({
        date: Vue.moment(date).format('YYYY-MM-DD'),
        debit: 'DEBIT' in dateByType ? dateByType['DEBIT'].reduce((a, b) => a + b.amount, 0) : 0,
        credit: 'CREDIT' in dateByType ? dateByType['CREDIT'].reduce((a, b) => a + b.amount, 0) : 0
      })
    }
    let startDate = Vue.moment().subtract(30, 'days')
    let endDate = Vue.moment()
    var dt = startDate
    let length = dates.length
    // eslint-disable-next-line
    while (dt <= endDate) {
      let dateExists = false
      for (var i = 0; i < length; i++) {
        if (dates[i].date === dt.format('YYYY-MM-DD')) {
          dateExists = true
          break
        }
      }
      if (!dateExists) {
        dates.push({
          date: Vue.moment(dt).format('YYYY-MM-DD'),
          debit: 0,
          credit: 0
        })
      }
      dt.add(1, 'days')
    }
    dates.sort(function (a, b) {
      return Vue.moment(a.date) - Vue.moment(b.date)
    })

    let transactionsByType = groupBy(transactions, 'type')
    let debits = groupBy(transactionsByType['DEBIT'], 'category')
    let credits = groupBy(transactionsByType['CREDIT'], 'category')
    let debitsDistribution = []
    for (let debit in debits) {
      debitsDistribution.push({key: debit, amount: Math.abs(debits[debit].reduce((a, b) => a + b.amount, 0))})
    }
    let creditsDistribution = []
    for (let credit in credits) {
      creditsDistribution.push({key: credit, amount: Math.abs(credits[credit].reduce((a, b) => a + b.amount, 0))})
    }
    let routes = [
      // login
      {
        method: 'POST',
        url: 'users/tokens',
        response: {token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJtb25leSIsImV4cCI6MTc2MzEwNDEwMCwic3ViIjoxLCJsb2dpbiI6Ik1pY2hhZWwiLCJzdGF0dXMiOnRydWUsIm1haWwiOiJtQGV5ZWZpbmQuaW5mbyIsInNjb3BlIjoidXNlciBhZG1pbiJ9.SN3ucVwQTuRwqThqW092Yz8z0cVYoGWLrK8AzMo7nfE'}
      },
      // users management
      {
        method: 'GET',
        url: 'users',
        response: [
          {'sub': 1, login: 'Michael', status: true, mail: 'm@eyefind.info', scope: 'user admin'},
          {'sub': 2, login: 'Trevor', status: false, mail: 'tpi@eyefind.info', scope: 'user'},
          {'sub': 3, login: 'Franklin', status: true, mail: 'frankie@eyefind.info', scope: 'user'}
        ]
      },
      {
        method: 'PUT',
        url: 'users/1',
        response: {sub: 1, login: 'Michael', 'status': true, mail: 'm@eyefind.info', scope: 'user admin'}
      },
      {
        method: 'PUT',
        url: 'users/2',
        response: {sub: 1, login: 'Trevor', 'status': false, mail: 'tpi@eyefind.info', scope: 'user'}
      },
      {
        method: 'PUT',
        url: 'users/3',
        response: {sub: 1, login: 'Franklin', 'status': true, mail: 'frankie@eyefind.info', scope: 'user'}
      },
      {
        method: 'GET',
        url: 'users/1/tokens',
        response: [
          {creation: Vue.moment().subtract(1, 'days'), ip: '10.0.0.2', userAgent: 'Mozilla/5.0 (X11; Linux x86_64; rv:63.0) Gecko/20100101 Firefox/63.0'},
          {creation: Vue.moment().subtract(41242, 'minutes'), ip: '10.0.0.13', userAgent: 'Mozilla/5.0 (Android 8.0.0; Mobile; rv:63.0) Gecko/63.0 Firefox/63.0'},
          {creation: Vue.moment().subtract(1044887, 'seconds'), ip: '10.0.0.14', userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53'}
        ]
      },
      // home
      {
        method: 'GET',
        url: 'transactions',
        response: transactions
      },
      {
        method: 'GET',
        url: 'transactions/history',
        response: { values: dates, periodStart: Vue.moment().subtract(30, 'days'), periodEnd: Vue.moment() }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/credit/categories',
        response: { values: creditsDistribution, periodStart: Vue.moment().subtract(30, 'days'), periodEnd: Vue.moment(), type: 'credit', key: 'categories' }
      },
      {
        method: 'GET',
        url: 'transactions/distribution/debit/categories',
        response: { values: debitsDistribution, periodStart: Vue.moment().subtract(30, 'days'), periodEnd: Vue.moment(), type: 'debit', key: 'categories' }
      },
      // accounts
      {
        method: 'GET',
        url: 'accounts',
        response: [
          {'id': 1, user: '1', bankId: '14000', branchId: '00170', accountId: '02437797004', label: 'Maze Bank', balance: 1635, lastUpdate: Vue.moment()}
        ]
      },
      // account 1
      {
        method: 'GET',
        url: 'accounts/1',
        response: {'id': 1, user: '1', bankId: '14000', branchId: '00170', accountId: '02437797004', label: 'Maze Bank', balance: 1635, lastUpdate: Vue.moment()}
      },
      // account 1 > transactions
      {
        method: 'GET',
        url: 'accounts/1/transactions',
        response: transactions
      },
      // about
      {
        method: 'GET',
        url: 'setup/versions/latest',
        response: {installed: '0.3.2', latest: '0.3.2'}
      },
      // categories
      {
        method: 'GET',
        url: 'categories',
        response: [
          { id: 1, label: 'Transports', status: true, icon: 'fa-train', sub: [ { id: 11, label: 'Public transports', status: true, icon: null, parentId: 1 }, { id: 12, label: 'Fuels', status: true, icon: null, parentId: 1 }, { id: 13, label: 'Insurance', status: true, icon: null, parentId: 1 }, { id: 14, label: 'Maintenance', status: true, icon: null, parentId: 1 } ] },
          { id: 2, label: 'Hobbies', status: true, icon: 'fa-beer', sub: [ { id: 20, label: 'Restaurant', status: true, icon: null, parentId: 2 }, { id: 21, label: 'Drink', status: true, icon: null, parentId: 2 } ] },
          { id: 3, label: 'Income', status: true, icon: 'fa-briefcase', sub: [ { id: 31, label: 'Wages', status: true, icon: null, parentId: 3 }, { id: 32, label: 'Reward', status: true, icon: null, parentId: 3 }, { id: 33, label: 'Sells', status: true, icon: null, parentId: 3 }, { id: 34, label: 'Gifts', status: false, icon: null, parentId: 3 } ] },
          { id: 4, label: 'Home', status: true, icon: 'fa-home', sub: [ { id: 41, label: 'Insurance', status: true, icon: null, parentId: 4 }, { id: 42, label: 'Rent', status: true, icon: null, parentId: 4 }, { id: 43, label: 'Mortgage', status: false, icon: null, parentId: 4 }, { id: 44, label: 'Furnishing', status: true, icon: null, parentId: 4 }, { id: 45, label: 'Electricity', status: true, icon: null, parentId: 4 }, { id: 46, label: 'Water', status: true, icon: null, parentId: 4 }, { id: 47, label: 'Internet', status: true, icon: null, parentId: 4 } ] },
          { id: 5, label: 'Clothing', status: true, icon: 'fa-user-secret', sub: [ { id: 51, label: 'Clothes', status: true, icon: null, parentId: 5 }, { id: 52, label: 'Shoes', status: true, icon: null, parentId: 5 } ] }
        ]
      },
      // categories
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
      if (!routeExists) {
        console.log(url)
        return request.respondWith({status: 404, statusText: 'Not found'})
      } else {
        return request.respondWith(
          route.response,
          {status: 200}
        )
      }
    })
  }
}
