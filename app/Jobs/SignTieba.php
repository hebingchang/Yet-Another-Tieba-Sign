<?php

namespace App\Jobs;

use App\BaiduAccount;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\TiebaApiController;
use App\SignRecord;
use App\UserForum;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Imtigger\LaravelJobStatus\Trackable;

class SignTieba implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public $bduss_id = "";
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bduss_id)
    {
        $this->prepareStatus();
        $this->bduss_id = $bduss_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bduss_id = $this->bduss_id;
        $forums = UserForum::where("bduss_id", $bduss_id)->get();
        $baidu_api = new TiebaApiController(BaiduAccount::where("id", $bduss_id)->first()->bduss);
        $this->setProgressMax($forums->count() + 1);

        $sign_succ_count = 0;
        $sign_fail_count = 0;

        foreach ($forums as $index => $forum) {
            $record = SignRecord::whereDate('created_at', Carbon::today())->where("forum_id", $forum->id);
            if (!$record->where("has_signed", true)->count()) {
                $result = $baidu_api->tiebaSign($forum->forum_name);
                $has_signed = false;

                if ($result->error_code == "0" || $result->error_code == "160002") {
                    $has_signed = true;
                    $sign_succ_count++;
                } else {
                    $record = SignRecord::whereDate('created_at', Carbon::today())->firstOrNew(["forum_id" => $forum->id]);
                    $record->forum_id = $forum->id;
                    $record->error_msg = $result->error_msg;
                    $record->has_signed = false;
                    $record->save();
                    $sign_fail_count++;
                }

                if ($result->error_code == "0") {
                    $record = SignRecord::whereDate('created_at', Carbon::today())->firstOrNew(["forum_id" => $forum->id]);
                    $record->forum_id = $forum->id;
                    $record->sign_time = Carbon::createFromTimestamp($result->user_info->sign_time);
                    $record->user_sign_rank = $result->user_info->user_sign_rank;
                    $record->cont_sign_num = $result->user_info->cont_sign_num;
                    $record->total_sign_num = $result->user_info->total_sign_num;
                    $record->sign_bonus_point = $result->user_info->sign_bonus_point;
                    $record->level_name = $result->user_info->level_name;
                    $record->levelup_score = $result->user_info->levelup_score;
                    $record->has_signed = $has_signed;
                    $record->save();
                }

                if ($result->error_code == "160002") {
                    $record = SignRecord::whereDate('created_at', Carbon::today())->firstOrNew(["forum_id" => $forum->id]);
                    $record->forum_id = $forum->id;
                    $record->error_msg = $result->error_msg;
                    $record->has_signed = $has_signed;
                    $record->save();
                }

            }

            $this->setProgressNow($index + 1);

            sleep(env("SIGN_SLEEP_TIME"));

        }

        $this->setProgressNow($forums->count() + 1);

        $api = new ApiController();
        $api->updateForums($bduss_id);

        $this->setOutput(['success' => $sign_succ_count, 'failed' => $sign_fail_count]);

    }
}
