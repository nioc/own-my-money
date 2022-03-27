import Axios from 'axios'
import Config from '@/services/Config'

export const http = Axios.create({
  baseURL: Config.API_URL,
})

export function setHeader(name, value) {
  delete http.defaults.headers.common[name]
  if (value) {
    http.defaults.headers.common[name] = value
  }
}

export function addResponseInterceptor(successHandler, errorHandler) {
  http.interceptors.response.use(successHandler, errorHandler)
}
