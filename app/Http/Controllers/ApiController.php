<?php

namespace App\Http\Controllers;

use App\BaiduAccount;
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
        if ($user_info->error_code == "0") {
            $baidu_account = BaiduAccount::firstOrNew(["user_id" => Auth::user()->id], ["bduss" => $bduss]);
            $baidu_account->baidu_id = $user_info->user->id;
            $baidu_account->baidu_name = $user_info->user->name;
            $baidu_account->save();
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
}
