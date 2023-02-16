import Vue from "vue";

// Vue Material
import VueMaterial from 'vue-material'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'

Vue.use(VueMaterial)

//AXIOS
import axios from 'axios'
import VueAxios from 'vue-axios'

Vue.use(VueAxios, axios)

// COMPONENTS
Vue.component('vue-order-items', require('./vue-components/VueOrderItems.vue').default);

let vm = new Vue({
    el: '.app',
    mounted() {

    }
})