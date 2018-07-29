<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yet Another Tieba Sign</title>
    <style>
        .el-header {
            background-color: #409EFF;
            color: #FFFFFF;
            line-height: 60px;
        }
        .app-title {
            font-size: 1.4em;
            margin-left: 1em;
        }
        .page-title {
            font-weight: 300;
            font-size: 2em;
            border-bottom: 1px solid #b9bbbe;
            padding-bottom: 0.6em;
        }
        html, body, #app, .el-container {
            height: 100% !important;
        }

    </style>
</head>
<body style="margin: 0">
<div id="app">
    <el-container style="height: 500px; border: 1px solid #eee">
        <el-header style="clear: both;">
            <el-row type="flex" class="row-bg" justify="space-between">
                <el-col :span="6">
                    <div class="app-title">
                        Tieba Sign
                    </div>
                </el-col>
                <el-col :span="6">
                    <div style="text-align: right">
                        <el-dropdown>
                            <i class="el-icon-setting" style="margin-right: 15px; color: #FFFFFF"></i>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item>注销</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                        <span>{{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
                    </div>
                </el-col>
            </el-row>

        </el-header>
        <el-container>
            <el-aside width="300px" style="background-color: rgb(238, 241, 246)">
                <nav-bar></nav-bar>
            </el-aside>
            <transition name="el-fade-in" mode="out-in" appear>
                <router-view></router-view>
            </transition>
        </el-container>
    </el-container>



</div>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>