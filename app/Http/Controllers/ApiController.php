<?php

namespace App\Http\Controllers;

use App\BaiduAccount;
use App\InvitationCode;
use App\Jobs\SignTieba;
use App\SignJob;
use App\SignRecord;
use App\User;
use App\UserForum;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Imtigger\LaravelJobStatus\JobStatus;
use Lcobucci\JWT\Parser;
use Predis\Response\ResponseInterface;

class ApiController extends Controller
{
    public function showLogin()
    {
        return view("welcome");
    }

    public function login(Request $request) {
        $username = $request->username;
        $password = $request->password;

        if (Auth::attempt([
            "name" => $username,
            "password" => $password,
        ])) {
            return redirect()->to("/home");
        } else {
            return redirect()->to("/?msg=用户名或密码错误");
        }
    }

    public function register(Request $request)
    {
        $username = $request->username;
        $password = $request->password;
        $invitation_code = $request->invitation_code;

        $code = InvitationCode::where('code', $invitation_code);
        if ($code->count()) {
            if (User::where("name", $username)->count()) {
                return Response::json([
                    "success" => false,
                    "err_msg" => '用户已存在'
                ]);
            } else {
                $user = new User([
                    "name" => $username,
                    "email" => $username . '@boar.tech',
                    "password" => Hash::make($password),
                    "roles" => "user"
                ]);
                $user->save();
                $code = $code->first();
                $code->has_used = true;
                $code->used_user_id = $user->id;
                $code->save();
                return Response::json([
                    "success" => true,
                ]);
            }
        } else {
            return Response::json([
                "success" => false,
                "err_msg" => "邀请码不存在"
            ]);
        }
    }

    public function ApiBindBDUSS(Request $request) {
        $bduss = $request->bduss;

        $baidu_api = new TiebaApiController($bduss);
        $user_info = $baidu_api->login();
        if (BaiduAccount::where("user_id", "<>", Auth::user()->id)->where("bduss", $bduss)->count() != 0) {
            return Response::json([
                "success" => false,
                "err_msg" => "该 BDUSS 已被他人绑定"
            ]);
        }
        if ($user_info->error_code == "0") {
            $baidu_account = BaiduAccount::firstOrNew(["user_id" => Auth::user()->id], ["bduss" => $bduss]);
            $baidu_account->baidu_id = $user_info->user->id;
            $baidu_account->baidu_name = $user_info->user->name;
            $baidu_account->save();

            UserForum::where('bduss_id', $baidu_account->id)->delete();
            $forums = $baidu_api->getFavForums();
            foreach ($forums as $forum) {
                $forum = new UserForum([
                    "bduss_id" => $baidu_account->id,
                    "forum_id" => $forum->id,
                    "forum_name" => $forum->name,
                    "level_id" => $forum->level_id,
                    "level_name" => $forum->level_name,
                    "cur_score" => $forum->cur_score,
                ]);
                $forum->save();
            }

            return Response::json([
                "success" => true,
                "user_info" => $user_info
            ]);
        } else {
            return Response::json([
                "success" => false,
                "err_msg" => "BDUSS 无效"
            ]);
        }

    }

    public function ApiGetBDUSS()
    {
        $baidu_accounts = Auth::user()->bduss;
        foreach ($baidu_accounts as $account) {
            $account->bduss = str_limit($account->bduss, 10);
        }

        return Response::json([
            "success" => true,
            "user" => Auth::user(),
            "data" => $baidu_accounts
        ]);
    }

    public function ApiDeleteBDUSS(Request $request)
    {
        $id = $request->id;
        $bduss = BaiduAccount::where("id", $id)->where("user_id", Auth::user()->id);
        if ($bduss->count() == 0) {
            return Response::json([
                "success" => false,
                "err_msg" => "没有该记录"
            ]);
        } else {
            $bduss->first()->forceDelete();
            return Response::json([
                "success" => true,
            ]);
        }
    }

    public function ApiGetForums(Request $request)
    {
        $bduss_id = $request->bduss_id;
        $forums = UserForum::where("bduss_id", $bduss_id)->get();

        return Response::json([
            "success" => true,
            "data" => $forums
        ]);
    }

    public function updateForums($bduss_id)
    {
        $bduss = BaiduAccount::where("id", $bduss_id)->first()->bduss;

        if (isset($bduss)) {
            $baidu_api = new TiebaApiController($bduss);
            $forums = $baidu_api->getFavForums();
            if (!isset($forums)) {
                return [
                    "success" => false,
                    "err_msg" => "BDUSS 已失效"
                ];
            }
            foreach ($forums as $forum) {
                $forum_record = UserForum::firstOrCreate(["bduss_id" => $bduss_id, "forum_id" => $forum->id]);
                $forum_record->forum_name = $forum->name;
                $forum_record->level_id = $forum->level_id;
                $forum_record->level_name = $forum->level_name;
                $forum_record->cur_score = $forum->cur_score;

                $forum_record->save();
            }

            $user_forums = UserForum::where("bduss_id", $bduss_id)->get();
            foreach ($user_forums as $user_forum) {
                $actual_forum = array_filter(
                    $forums,
                    function ($e) use ($user_forum) {
                        return $e->id == $user_forum->forum_id;
                    }
                );

                if (sizeof($actual_forum) == 0) {
                    $user_forum->delete();
                }

            }
            return [
                "success" => true,
            ];
        } else {
            return [
                "success" => false,
                "err_msg" => "内部错误"
            ];
        }
    }

    public function ApiUpdateForums(Request $request)
    {
        return Response::json($this->updateForums($request->bduss_id));
    }

    public function ApiBDUSSSign($bduss_id)
    {
        $job = new SignTieba($bduss_id);
        $this->dispatch($job);
        $jobStatusId = $job->getJobStatusId();
        $sign_job = new SignJob([
            "bduss_id" => $bduss_id,
            "job_id" => $jobStatusId,
        ]);
        $sign_job->save();

        return Response::json([
            "success" => true,
            "job_id" => $jobStatusId
        ]);
    }

    public function ApiJobStatus($job_id)
    {
        return Response::json([
            "success" => true,
            "data" => JobStatus::find($job_id)
        ]);
    }

    public function ApiSignRecord(Request $request)
    {
        $bduss_id = $request->bduss_id;
        $date = $request->date;

        if (!isset($date)) {
            $date = Carbon::now()->toDateString();
        }

        $forums = UserForum::where('bduss_id', $request->bduss_id)->get();
        $records = [];

        // TODO: Implement the function with Eloquent
        foreach ($forums as $forum) {
            $forum->sign_history = array();
            foreach ($forum->sign_status as $record) {
                if (Carbon::parse($record->created_at)->toDateString() == Carbon::parse($date)->toDateString()) {
                    $forum->sign_history = $record;
                }
            }
            unset($forum->sign_status);
        }

        return Response::json([
            "success" => true,
            "data" => $forums
        ]);

    }

    public function refreshJob($job)
    {
        $job_id = $job->job_id;
        $job_status = JobStatus::find($job_id);
        $job->status = $job_status->status;
        $job->save();
    }

    public function ApiListJobs($bduss_id)
    {
        $jobs = SignJob::where("bduss_id", $bduss_id)->get();
        foreach ($jobs as $job) {
            if ($job->status == "queued" || $job->status == "executing") {
                $this->refreshJob($job);
            }
        }
        return Response::json([
            "success" => true,
            "data" => $jobs
        ]);
    }

    public function ApiListOngoingJobs($bduss_id)
    {
        $jobs = SignJob::where("bduss_id", $bduss_id)->where("status", "executing")->orWhere("status", "queued")->get();
        foreach ($jobs as $job) {
            $this->refreshJob($job);
        }
        $jobs = SignJob::where("bduss_id", $bduss_id)->where("status", "executing")->orWhere("status", "queued")->get();
        return Response::json([
            "success" => true,
            "data" => $jobs
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $json = [
            'success' => true,
            'code' => 200,
            'message' => 'You are Logged out.',
        ];
        return response()->json($json, '200');
    }

    public function ApiChangePassword(Request $request)
    {
        $old_password = $request->old_password;
        $new_password = $request->new_password;
        $confirm_password = $request->confirm_password;

        $user = User::find(Auth::user()->id);

        if (!Hash::check($old_password, $user->password)) {
            return Response::json([
                "success" => false,
                "err_msg" => "原密码不正确\n"
            ]);
        }

        if ($new_password != $confirm_password)
        {
            return Response::json([
                "success" => false,
                "err_msg" => "新密码不匹配"
            ]);
        }

        $user->password = Hash::make($new_password);
        $user->save();

        return Response::json([
            "success" => true,
        ]);


    }

    public function ApiInvCodeList()
    {
        $codes = InvitationCode::where('apply_user_id', Auth::user()->id)->get();
        foreach ($codes as $code)
        {
            $code->used_user;
        }
        return Response::json([
            'success' => true,
            'data' => $codes
        ]);
    }

    public function ApiInvCodeAdd()
    {
        $code = str_random(16);

        if (Auth::user()->roles == "admin") {
            $inv_code = new InvitationCode([
                "apply_user_id" => Auth::user()->id,
                "code" => $code,
            ]);
            $inv_code->save();
            return Response::json([
                "success" => true,
                "data" => $inv_code
            ]);
        } else {
            if (InvitationCode::where("apply_user_id", Auth::user()->id)->count() >= config("app.user_max_invitation")) {
                return Response::json([
                    "success" => false,
                    "err_msg" => "邀请码数量已达上限"
                ]);
            } else {
                $inv_code = new InvitationCode([
                    "apply_user_id" => Auth::user()->id,
                    "code" => $code,
                ]);
                $inv_code->save();
                return Response::json([
                    "success" => true,
                    "data" => $inv_code
                ]);
            }
        }
    }

    public function ApiInvCodeVerify(Request $request)
    {
        $invitation_code = $request->invitation_code;
        $code = InvitationCode::where("code", $invitation_code);
        if ($code->count()) {
            return Response::json([
                "success" => true,
                "data" => $code->first()->apply_user->name
            ]);
        } else {
            return Response::json([
                "success" => false,
            ]);
        }
    }

    public function ApiBaiduQR()
    {
        $qr_data = json_decode(file_get_contents('https://passport.baidu.com/v2/api/getqrcode?lp=pc&apiver=v3&tpl=tieba'));
        return Response::json([
            "success" => true,
            "data" => $qr_data
        ]);
    }

    public function ApiBaiduQRPoll(Request $request)
    {
        $sign = $request->sign;

        $client = new Client([
            // You can set any number of default request options.
            'timeout'  => 60,
            'defaults' => [
                'headers' => [
                    'Connection' => 'keep-alive',
                    'Cache-Control' => 'max-age=0',
                    'Upgrade-Insecure-Requests' => '1',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept-Language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
                    'Referer' => 'tieba.baidu.com'
                ]
            ]
        ]);

        try {
            $response = $client->get("https://passport.baidu.com/channel/unicast", [
                "query" => [
                    "channel_id" => $sign,
                    "callback" => "",
                    "tpl" => "tieba",
                    "apiver" => "v3",
                    "tt" => time() . '0000',
                    "_" => time() . '0003'
                ]
            ]);

            return Response::json([
                "success" => true,
                "data" => json_decode(json_decode(str_replace(")", "", str_replace("(", "", $response->getBody())))->channel_v)
            ]);

        } catch (\Exception $e) {
            return Response::json([
                "success" => false,
                "err_msg" => $e->getMessage()
            ]);
        }

    }

    public function ApiBaiduQRBDUSS(Request $request)
    {
        $v = $request->v;

        $client = new Client([
            // You can set any number of default request options.
            'timeout'  => 1,
            'defaults' => [
                'headers' => [
                    'Connection' => 'keep-alive',
                    'Cache-Control' => 'max-age=0',
                    'Upgrade-Insecure-Requests' => '1',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept-Language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
                    'Referer' => 'tieba.baidu.com'
                ]
            ]
        ]);

        $response = $client->get("https://passport.baidu.com/v3/login/main/qrbdusslogin", [
            "query" => [
                "v" => time() . "0000",
                "bduss" => $v,
                "u" => "https://pan.baidu.com/disk/home",
                "loginVersion" => "v4",
                "qrcode" => "1",
                "traceid" => "",
                "apiver" => "v3",
                "tt" => time() . '0000',
                "callback" => ""
            ]
        ]);

        $cookies = $response->getHeader("Set-Cookie");
        $bduss = "";
        foreach ($cookies as $cookie) {
            if (substr($cookie, 0, 5) == "BDUSS") {
                $bduss = explode("=", explode(";", $cookie)[0])[1];
            }
        }

        return Response::json([
            "success" => true,
            "data" => $bduss,
        ]);
    }

    public function ApiDashboard()
    {
        $accounts = BaiduAccount::where("user_id", Auth::user()->id)->get();
        $account_count = $accounts->count();
        $forums_count = 0;
        $forums_ids = [];
        foreach ($accounts as $account) {
            $forums = UserForum::where('bduss_id', $account->id)->get();
            $forums_count += $forums->count();
            foreach ($forums as $forum) {
                $forums_ids[] = $forum->id;
            }
        }
        $days_count = SignRecord::whereIn("forum_id", $forums_ids)->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            })->count();

        $added_exp = 0;
        foreach (SignRecord::whereIn("forum_id", $forums_ids)->get() as $record)
        {
            $added_exp += $record->sign_bonus_point;
        }

        return Response::json([
            "success" => true,
            "data" => [
                "accounts_count" => $account_count,
                "forums_count" => $forums_count,
                "days_count" => $days_count,
                "added_exp" => $added_exp,
            ]
        ]);

    }

}
