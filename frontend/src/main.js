import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.component('VueMultiselect', VueMultiselect)
app.mount('#app')
