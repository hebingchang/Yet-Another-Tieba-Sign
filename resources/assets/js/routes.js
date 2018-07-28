import HomePage from './components/HomePage.vue'
import BindBduss from './components/BindBduss.vue'
import UserForums from './components/UserForums'

const routes = [
    { path: '/', component: HomePage },
    { path: '/bind', component: BindBduss },
    { path: '/forums', component: UserForums }
];

export default routes;