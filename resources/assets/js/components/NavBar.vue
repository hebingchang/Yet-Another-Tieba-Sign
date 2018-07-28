<template>
    <el-menu
            class="el-menu-vertical-demo"
            @open="handleOpen"
            @close="handleClose"
            router="true">
        <el-submenu index="1">
            <template slot="title">
                <i class="el-icon-location"></i>
                <span>百度账号管理</span>
            </template>
            <el-menu-item index="1-1" route="/bind">绑定 BDUSS</el-menu-item>
            <el-menu-item index="1-2" route="/forums">管理贴吧</el-menu-item>
        </el-submenu>
        <el-menu-item index="2" route="/">
            <i class="el-icon-menu"></i>
            <span slot="title">导航二</span>
        </el-menu-item>
        <el-menu-item index="3" route="/bind">
            <i class="el-icon-document"></i>
            <span slot="title">导航三</span>
        </el-menu-item>
        <el-menu-item index="4">
            <i class="el-icon-setting"></i>
            <span slot="title">导航四</span>
        </el-menu-item>
    </el-menu>
</template>

<script>
    export default {
        name: "NavBar"
    }
</script>

<style scoped>

</style>