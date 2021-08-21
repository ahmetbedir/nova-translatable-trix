import Vue from 'vue'

import FormField from './components/FormField';
import DetailField from './components/DetailField';


Nova.booting((Vue, router, store) => {
  Vue.component('detail-translatable-trix', DetailField)
  Vue.component('form-translatable-trix', FormField)
})
