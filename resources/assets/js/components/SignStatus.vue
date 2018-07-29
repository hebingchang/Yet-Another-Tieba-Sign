<template>

    <el-main v-loading="loading">
        <h1 class="page-title">签到状态</h1>

        <el-card class="box-card">
            <div slot="header" class="clearfix">
                <span>签到记录</span>
            </div>
            <div class="text item">
                <el-form ref="form" :inline="true" :model="form" label-width="80px">
                    <el-form-item label="百度账号">
                        <el-select v-model="bduss_id" placeholder="请选择账户" @change="refresh">
                            <el-option :label="item.baidu_name" :value="item.id" v-for="item in bdusses" :key="item.id"></el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="日期">
                        <el-date-picker
                                v-model="date"
                                value-format="yyyy-MM-dd"
                                align="right"
                                type="date"
                                placeholder="选择日期"
                                :picker-options="pickerOptions1"
                                @change="refresh">
                        </el-date-picker>
                    </el-form-item>

                </el-form>

                <el-form ref="form">
                    <el-form-item v-if="bduss_id != null">
                        <el-button type="primary" @click="sign">立即签到</el-button>
                    </el-form-item>
                </el-form>

                <el-table
                        :data="records"
                        style="width: 100%"
                        :default-sort = "{prop: 'sign_history', order: 'descending'}"
                        v-if="!signing"
                >

                    <el-table-column
                            label="贴吧名称"
                            sortable
                            fixed>
                        <template slot-scope="scope">
                            <a :href="'http://tieba.baidu.com/f?kw=' + scope.row.forum_name" style="text-decoration: none;" class="el-button el-button--text" target="_blank"><span>{{ scope.row.forum_name }}</span></a>
                        </template>
                    </el-table-column>

                    <el-table-column
                            prop="level_name"
                            label="等级"
                            sortable>
                    </el-table-column>

                    <el-table-column
                            prop="cur_score"
                            label="当前积分"
                            sortable>
                    </el-table-column>

                    <el-table-column
                            label="签到情况"
                            sortable>
                        <template slot-scope="scope">
                            {{ scope.row.sign_history.has_signed ? "已签到" : "未签到" }}
                        </template>
                    </el-table-column>

                </el-table>

            </div>
        </el-card>
    </el-main>

</template>

<script>
    export default {
        name: "SignStatus",
        data: function () {
            return {
                pickerOptions1: {
                    disabledDate(time) {
                        return time.getTime() > Date.now();
                    },
                    shortcuts: [{
                        text: '今天',
                        onClick(picker) {
                            picker.$emit('pick', new Date());
                        }
                    }, {
                        text: '昨天',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', date);
                        }
                    }, {
                        text: '一周前',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit('pick', date);
                        }
                    }]
                },
                date: null,
                bduss_id: null,
                bdusses: [],
                records: [],
                show_sign: false,
                signing: false,
            }
        },
        created: function () {
            this.$http.get('/api/v1/bduss/get').then(function (res) {
                this.bdusses = res.body.data
            });
        },
        methods: {
            refresh: function () {
                if (this.bduss_id !== null && this.date !== null) {
                    this.show_sign = true
                    this.$http.get('/api/v1/sign/record/' + this.bduss_id + '/' + this.date).then(function (res) {
                        this.records = res.body.data
                    });
                }
            },
            sign: function () {
                this.$http.get('/api/v1/bduss/' + this.bduss_id + '/sign').then(function (res) {
                    this.records = res.body.data
                });
            }
        }
    }
</script>

<style scoped>

</style>