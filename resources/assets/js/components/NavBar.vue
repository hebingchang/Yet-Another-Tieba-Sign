<template>
    <el-menu
            class="el-menu-vertical-demo"
            @open="handleOpen"
            @close="handleClose"
            router="true">
        <el-submenu index="1">
            <template slot="title">
                <i class="el-icon-menu"></i>
                <span>百度账号管理</span>
            </template>
            <el-menu-item index="1-1" route="/bind">绑定 BDUSS</el-menu-item>
            <el-menu-item index="1-2" route="/forums">管理贴吧</el-menu-item>
        </el-submenu>
        <el-submenu index="2">
            <template slot="title">
                <i class="el-icon-edit-outline"></i>
                <span>签到管理</span>
            </template>
            <el-menu-item index="2-1" route="/sign">签到记录</el-menu-item>
        </el-submenu>
    </el-menu>
</template>

<script>
    export default {
        name: "NavBar"
    }
</script>

<style scoped>

</style>