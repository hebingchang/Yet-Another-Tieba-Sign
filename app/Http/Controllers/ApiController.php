<?php

namespace App\Http\Controllers;

use App\BaiduAccount;
use App\UserForum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

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

    public function ApiUpdateForums(Request $request)
    {
        $bduss = BaiduAccount::where("id", $request->bduss_id)->first()->bduss;
        if (isset($bduss)) {
            $baidu_api = new TiebaApiController($bduss);
            UserForum::where('bduss_id', $request->bduss_id)->delete();
            $forums = $baidu_api->getFavForums();
            if (!isset($forums)) {
                return Response::json([
                    "success" => false,
                    "err_msg" => "BDUSS 已失效"
                ]);
            }
            foreach ($forums as $forum) {
                $forum = new UserForum([
                    "bduss_id" => $request->bduss_id,
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
            ]);
        } else {
            return Response::json([
                "success" => false,
                "err_msg" => "内部错误"
            ]);
        }
    }
}
