import HomePage from './components/HomePage.vue'
import BindBduss from './components/BindBduss.vue'
import UserForums from './components/UserForums'
import SignStatus from './components/SignStatus'

const routes = [
    { path: '/', component: HomePage },
    { path: '/bind', component: BindBduss },
    { path: '/forums', component: UserForums },
    { path: '/sign', component: SignStatus}
];

export default routes;