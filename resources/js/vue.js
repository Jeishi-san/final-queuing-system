import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import HomeView from './components/vue/HomeView.vue';
import QueueView from './components/vue/QueueView.vue';

import LoginView from './components/vue/LoginView.vue';
import RegisterView from './components/vue/RegisterView.vue';
import AdminQueueView from './components/vue/AdminQueueView.vue';

import DashboardView from './components/vue/dashboard/Dashboard.vue';

// Font Awesome imports
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';



// Add icons to the library
library.add(fas);
library.add(far);











// HomeView
if (document.querySelector('#home-app')) {
    const app = createApp(HomeView);
    app.component('FontAwesomeIcon', FontAwesomeIcon);
    app.mount('#home-app');
}

// QueueView
if (document.querySelector('#queue-app')) {
    const app = createApp(QueueView);
    app.component('FontAwesomeIcon', FontAwesomeIcon);
    app.mount('#queue-app');
}

// AdminQueueView
if (document.querySelector('#admin-queue-app')) {
    const app = createApp(AdminQueueView);
    app.component('FontAwesomeIcon', FontAwesomeIcon);
    app.mount('#admin-queue-app');
}




// LoginView
if (document.querySelector('#login-app')) {
    const app = createApp(LoginView);
    app.component('FontAwesomeIcon', FontAwesomeIcon);
    app.mount('#login-app');
}


// RegisterView
if (document.querySelector('#register-app')) {
    const app = createApp(RegisterView);
    app.component('FontAwesomeIcon', FontAwesomeIcon);
    app.mount('#register-app');
}




// DashboardView
if (document.querySelector('#dashboard-app')) {
    const app = createApp(DashboardView);
    app.component('FontAwesomeIcon', FontAwesomeIcon);
    app.mount('#dashboard-app');
}

















// just for test
// if (document.querySelector('#login-app')) {
//     const app = createApp(Login);
//     app.component('FontAwesomeIcon', FontAwesomeIcon);
//     app.mount('#login-app');
// }




// import { createApp, reactive } from 'vue';


// const app = createApp({});


// // Register globally
// const globalState = reactive({
//   loginSuccess: true
// });


// app.config.globalProperties.$systemName = 'IT Ops Queuing System';

// app.config.globalProperties.$globalState = globalState;
// /* in any component */
// /*

// <script setup>
// import { getCurrentInstance } from 'vue'
// const { appContext } = getCurrentInstance()
// const globalState = appContext.config.globalProperties.$globalState

// function onLoginSuccess() {
//   globalState.loginSuccess = true
// }
// </script>

// <template>
//   <div v-if="globalState.loginSuccess">
//     ✅ Logged in successfully!
//   </div>
// </template>


// */



// // Register FontAwesomeIcon component globally
// app.component('FontAwesomeIcon', FontAwesomeIcon);
// app.component('homeview', HomeView);


// app.mount('#app');
