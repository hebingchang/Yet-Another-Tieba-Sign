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
                    <el-form-item v-if="bduss_id != null && !signing">
                        <el-button type="primary" @click="sign">立即签到</el-button>
                    </el-form-item>
                    <el-form-item :label="signing_status" v-if="signing">
                        <el-progress :text-inside="true" :stroke-width="18" :percentage="progress"></el-progress>
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
                            {{ scope.row.sign_history.has_signed ? "已签到" : (scope.row.sign_history.error_msg ? "签到失败 ("+scope.row.sign_history.error_msg+")" : "未签到") }}
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
                progress: 0,
                job_id: null,
                refresh_job: null,
                signing_status: "",
            }
        },
        created: function () {
            this.$http.get('/api/v1/bduss/get').then(function (res) {
                this.bdusses = res.body.data
            });
        },
        methods: {
            refresh: function () {
                this.checkSigning()
                if (this.bduss_id !== null && this.date !== null) {
                    this.show_sign = true
                    this.$http.get('/api/v1/sign/record/' + this.bduss_id + '/' + this.date).then(function (res) {
                        this.records = res.body.data
                    });
                }
            },
            sign: function () {
                this.$http.get('/api/v1/bduss/' + this.bduss_id + '/sign').then(function (res) {
                    if (res.body.success === true) {
                        this.checkSigning()
                    }
                });
            },
            checkSigning: function () {
                this.$http.get('/api/v1/queue/list/' + this.bduss_id + '/ongoing').then(function (res) {
                    if (res.body.data.length !== 0) {
                        this.signing = true
                        this.job_id = res.body.data[0].job_id
                        this.refresh_job = setInterval(this.refreshSigning, 1000)
                    } else {
                        this.signing = false
                    }
                });
            },
            refreshSigning: function () {
                this.$http.get('/api/v1/queue/status/' + this.job_id).then(function (res) {
                    if (res.body.data.status === "queued" || res.body.data.status === "executing") {
                        if (res.body.data.status === "queued") {
                            this.signing_status = "签到任务队列中"
                            this.progress = 0
                        } else if (res.body.data.status === "executing") {
                            this.signing_status = "签到任务执行中"
                            this.progress = Math.round(res.body.data.progress_now / res.body.data.progress_max * 100 * 100) / 100
                        }
                        this.signing = true
                    } else {
                        this.signing = false

                        var today = new Date();
                        var dd = today.getDate();
                        var mm = today.getMonth()+1; //January is 0!
                        var yyyy = today.getFullYear();

                        this.date = yyyy + "-" + mm + "-" + dd
                        this.refresh()
                        clearInterval(this.refresh_job)
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>