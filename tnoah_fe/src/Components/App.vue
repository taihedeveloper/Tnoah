<template>
  <div id="app" class="cf">
    <Headers></Headers>
    <!-- <SideLeft></SideLeft> -->
    <!-- 路由 -->
    <router-view></router-view>
  </div>
</template>

<script>
import {mapGetters,mapActions} from 'vuex'
import Headers from './Headers.vue'
import SideLeft from './SideLeft.vue'

export default {
  name: 'app',
  data () {
    return {
      msg: 'Welcome to My Vue.js App'
    }
  },
  methods:mapActions([
    "increment"
  ]),
  components:{
    Headers,
    SideLeft
  },
  computed:mapGetters([
    'count',
    'inputvalue'
  ])
}
</script>
<style lang="less">
@import "../Css/common";
@import "../Css/dialog.less";
html,body{
  height: 100%;
  overflow: hidden;
  background: #f0f4f7;
}
#app{
  width: 100%;
}
</style>