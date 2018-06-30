import Vue from 'vue'
import Bus from './../services/Bus.js'
import Config from './../services/Config.js'

export default {

  // user object will let us check authentication status
  user: {
    authenticated: false,
    token: null,
    id: null,
    login: null
  },

  // send a request to the login URL and save the returned JWT
  login (context, creds, redirect) {
    context.$http.post(Config.API_URL + 'users/tokens', creds)
      .then(response => {
        // store token
        this.handleToken(response.body)
        // send event for updating UI
        Bus.$emit('user-logged', this.user)
        // redirect to the requested route
        if (redirect) {
          context.$router.replace(redirect)
          return
        }
        // redirect to home
        context.$router.replace({name: 'home'})
      }, response => {
        if (response.body.message) {
          context.error = response.body.message
          return
        }
        context.error = response.status + ' - ' + response.statusText
      })
  },

  // remove the token and redirect to login page
  logout (context) {
    localStorage.removeItem('user')
    this.user.authenticated = false
    delete Vue.http.headers.common['Authorization']
    context.$router.replace({name: 'login'})
    this.user = {}
    return this.user
  },

  // check if user is authenticated with a token
  checkAuth () {
    let token = this.getToken()
    if (token) {
      this.user.authenticated = true
    } else {
      this.user.authenticated = false
    }
  },

  // return the object to be passed as a header for authenticated requests
  getAuthHeader () {
    let token = this.getToken()
    if (token) {
      return 'Bearer ' + token
    }
    return null
  },

  // populate attributes with a payload
  populate (payload) {
    if (payload) {
      this.user.token = payload.token
      this.user.id = payload.id
      this.user.login = payload.login
      this.user.scope = payload.scope
      this.user.exp = payload.exp
      this.user.authenticated = true
    }
  },

  handleToken (data) {
    if (!data || !data.token) {
      // token is not provided in data
      return false
    }
    let parts = data.token.split('.')
    if (parts.length !== 3) {
      // token do not contains 3 parts (standard structure)
      return false
    }
    try {
      // parse the payload part
      let payload = JSON.parse(atob(parts[1]))
      // transform the payload and store it in local storage
      payload.token = data.token
      payload.id = payload.sub
      delete payload.sub
      localStorage.setItem('user', JSON.stringify(payload))
      // populate this User
      this.populate(payload)
      Vue.http.headers.common['Authorization'] = this.getAuthHeader()
      return true
    } catch (err) {
      console.error(err)
      // error during parsing
      return false
    }
  },

  // return user profile from token
  getProfile () {
    if (this.getToken()) {
      return JSON.parse(JSON.stringify(this.user))
    }
    return false
  },

  // return stored token
  getToken () {
    try {
      let payload = JSON.parse(localStorage.getItem('user'))
      this.populate(payload)
      return this.user.token
    } catch (e) {
      console.error(e)
      return false
    }
  }
}
