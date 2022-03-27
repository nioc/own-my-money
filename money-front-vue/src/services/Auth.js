import { http, setHeader } from '@/services/Http'

export default {

  // user object will let us check authentication status
  user: {
    authenticated: false,
    token: null,
    id: null,
    login: null,
  },

  // send a request to the login URL and save the returned JWT
  async login (creds) {
    const response = await http.post('users/tokens', creds)
    // store token
    this.handleToken(response.data)
    return this.user
  },

  // remove the token
  logout () {
    // clear storage
    localStorage.clear()
    sessionStorage.clear()
    // clear authentication status
    this.user.authenticated = false
    // delete authorization header
    setHeader('Authorization', null)
    // clear user
    this.user = {}
    return this.user
  },

  // return the object to be passed as a header for authenticated requests
  getAuthHeader () {
    const token = this.getToken()
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
      this.user.mail = payload.mail
      this.user.language = payload.language
      const scope = {}
      if (payload.scope) {
        payload.scope
          .split(' ')
          .forEach((role) => scope[role] = true)
      }
      this.user.scope = scope
      this.user.exp = payload.exp
      // check if token is not expired
      this.user.authenticated = (payload.exp > Math.floor(Date.now() / 1000))
    }
  },

  handleToken (data) {
    if (!data || !data.token) {
      // token is not provided in data
      return false
    }
    const parts = data.token.split('.')
    if (parts.length !== 3) {
      // token do not contains 3 parts (standard structure)
      return false
    }
    try {
      // parse the payload part
      const payload = JSON.parse(atob(parts[1]))
      // transform the payload and store it in local storage
      payload.token = data.token
      payload.id = payload.sub
      delete payload.sub
      localStorage.setItem('user', JSON.stringify(payload))
      // populate this User
      this.populate(payload)
      setHeader('Authorization', this.getAuthHeader())
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
      const payload = JSON.parse(localStorage.getItem('user'))
      this.populate(payload)
      return this.user.token
    } catch (e) {
      console.error(e)
      return false
    }
  },
}
