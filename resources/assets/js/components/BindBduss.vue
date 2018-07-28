<template>
    <el-main>
        <h1 class="page-title">绑定 BDUSS</h1>

        <el-card class="box-card">
            <div slot="header" class="clearfix">
                <span>绑定新的 BDUSS</span>
            </div>
            <div class="text item">
                <el-form ref="form" :model="form" label-width="80px">
                    <el-form-item label="BDUSS">
                        <el-input v-model="bduss"></el-input>
                    </el-form-item>

                    <el-form-item>
                        <el-button round v-on:click="bind">绑定</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>

        <el-card class="box-card">
            <div slot="header" class="clearfix">
                <span>已绑定的 BDUSS</span>
            </div>
            <div class="text item">
                <el-table
                        :data="bdusses"
                        style="width: 100%"
                        :default-sort = "{prop: 'created_at', order: 'descending'}"
                >
                    <el-table-column
                            prop="baidu_name"
                            label="百度ID"
                            sortable>
                    </el-table-column>
                    <el-table-column
                            label="BDUSS"
                            sortable
                            >
                        <template slot-scope="scope">
                            <code>{{ scope.row.bduss }}</code>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="created_at"
                            label="添加时间"
                            sortable>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button
                                    size="mini"
                                    type="danger"
                                    @click="handleDelete(scope.row)">删除</el-button>
                        </template>
                    </el-table-column>

                </el-table>
            </div>
        </el-card>

    </el-main>
</template>

<script>
    export default {
        name: "BindBduss",
        data: function () {
            return {
                bduss: "",
                bdusses: [],
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        },
        created: function () {
            this.refresh()
        },
        methods: {
            refresh: function () {
                this.$http.get('/api/v1/bduss/get').then(function (res) {
                    this.bdusses = res.body.data
                });
            },
            bind: function (event) {
                this.$http.post('/api/v1/bduss/bind', {
                    bduss: this.bduss,
                    _token: this.csrf
                }).then(function (res) {
                    if (res.body.success === false) {
                        this.$alert(res.body.err_msg, '绑定失败', {
                            confirmButtonText: '好',
                            callback: action => {
                                this.bduss = ""
                            }
                        });
                    } else {
                        this.$alert("您已绑定百度账号 " + res.body.user_info.user.name, '绑定成功', {
                            confirmButtonText: '好',
                            callback: action => {
                                this.bduss = ""
                                this.refresh()
                            }
                        });
                    }
                });
            },
            handleDelete: function (row) {
                this.$http.post('/api/v1/bduss/delete', {
                    id: row.id,
                    _token: this.csrf
                }).then(function (res) {
                    if (res.body.success === false) {
                        this.$alert(res.body.err_msg, '删除失败', {
                            confirmButtonText: '好',
                        });
                    } else {
                        this.$alert("BDUSS 删除成功", '删除成功', {
                            confirmButtonText: '好',
                            callback: action => {
                                this.refresh()
                            }
                        });
                    }
                });
            }
        }
    }
</script>

<style scoped>
    .box-card {
        margin-bottom: 1em;
    }
</style>