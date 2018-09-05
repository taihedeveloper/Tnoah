<template>
	<!--header start-->
	<header class="header header-noborder">
	    <div class="header-container clearfix">
	    	<!--logo标记-->
	    	<router-link to="/index" class="logo"></router-link>
	    	<!--用户信息 start-->
	        <div class="user-info mr">
                <div class="username mr-s" style="position: relative;">
                    <span>{{username}}</span>
                    <ul class="sub-nav t_right" style="right: 0;">
                        <!-- <li id="user-li"><a >个人中心</a></li> -->
                        <li><a href="http://monitor.taihenw.com/logout.php">安全退出</a></li>
                    </ul>
                </div>
	        </div>
	        <!--用户信息 end-->

	        <!--网站导航 start-->
	        <ul class="nav-list">
	            <li id="index-li" class="">
	            	<router-link to="/index">服务树</router-link>
	            </li>
	            <li id="index-li" class="">
	            	<router-link to="/serviceline">业务线配置</router-link>
	            </li>
	            <li id="manage-li" class="nav-ul-selected">
	            	<router-link to="/group">集群 | 报警配置</router-link>
	            </li>
	            <li id="manage-li" class="nav-ul-selected">
	            	<router-link to="/spark">计算任务管理</router-link>
	            </li>
	            <li id="manage-li" class="nav-ul-selected">
	            	<router-link to="/permission">通用权限服务</router-link>
	            </li>
	            <li id="manage-li" class="nav-ul-selected">
	            	<router-link to="/events">报警仪表盘</router-link>
	            </li>
	           <!--  <li id="manage-li" class="nav-ul-selected">
	            	<router-link to="/graph">图test</router-link>
	            </li> -->
	        </ul>
	        <!--网站导航 end-->
	    </div>
		<!-- <router-view></router-view> -->

	</header>
	<!--header end-->
</template>
<script>
import vueGetData from "../Js/vueGetData.js"
	export default {
	  name: 'Header',
	  data () {
	    return {
	      msg: 'header',
	      username:""
	    }
	  },
	  created(){
	  	this.username = vueGetData.username();
	  }
	}
</script>
<style>
.header {
	position: relative;
	height: 50px;
	line-height: 50px;
	width: 100%;
	z-index: 200;
	background: #fff;
}

.header.header-noborder {
	box-shadow: 0 1px 1px #ececec;
	border-bottom: 1px solid #ececec;
}

.header .logo {
	display: block;
	width: 240px;
	height: 52px;
	float: left;
	background: #20335d;
	background-image: url(/dist/Image/logo.png);
	background-repeat: no-repeat;
	background-size: 90%;
	background-position: 10px 5px;
	position: relative;
	z-index: 10;
}

.header-container {
	width: 100%;
	margin: 0 auto
}

.user-info {
	font-size: 13px;
	position: relative;
	float: right;
	min-width: 100px;
	padding-right: 10px;
}

.user-info .username {
	display: inline-block;
	vertical-align: middle
}

.user-info .username>span {
	width: 100px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	display: inline-block;
	line-height: 50px;
	color: #1f2d3d;
	cursor: pointer;
	vertical-align: middle;
	padding-right: 20px;
	background: url(/dist/Image/down_triangle.png) no-repeat;
	background-position: right center;
	font-size: 14px;
}

.user-info .username:hover>span {
	background: url(/dist/Image/up_triangle.png) no-repeat;
	background-position: right center
}

.user-info a {
	vertical-align: middle;
	color: #1f2d3d;
}

.nav-list {
	float: left;
	display: inline-block;
	margin-right: 40px;
	padding-top: 20px;
	padding-left: 15px;
	position: relative;
	z-index: 10;
}

.nav-list>li {
	float: left;
	text-align: center;
}

.nav-list li a {
	color: #989898;
	display: block;
	font-size: 14px;
	line-height: 30px;
	padding: 0 10px;
	border-bottom: 1px solid #989898;
	box-shadow: 0 1px 0px rgba(152,152,152,0.2);
}

.nav-list>li a:hover {
    border-bottom: 1px solid #2bc8f2;
	box-shadow: 0 1px 0px rgba(43,200,242,0.2);
    color: #2bc8f2;
}

.nav-list li a.router-link-active{
    border-bottom: 1px solid #2bc8f2;
	box-shadow: 0 1px 0px rgba(43,200,242,0.2);
    color: #2bc8f2;
}

.nav-list>span {
	float: left;
	line-height: 65px;
	width: 5px;
	text-align: center;
	color:#000;
	font-size: 18px;
	font-weight: bold;
}

.sub-nav {
	position: absolute;
	z-index: 10;
	padding: 5px 0;
	border: 1px solid #eef1f6;
	display: none;
	border-top: none;
	box-sizing: border-box;
	min-width: 100px;
	background: #fff;
}

.sub-nav li>a {
	display: block;
	line-height: 30px;
	text-align: left;
	padding: 0 20px;
	color: #333;
	font-size: 13px
}

.sub-nav li>a:hover {
	background: #ecf3fe;
	color: #4085f4
}

.username:hover .sub-nav,
.nav-list>li:hover .sub-nav {
	display: block
}
</style>