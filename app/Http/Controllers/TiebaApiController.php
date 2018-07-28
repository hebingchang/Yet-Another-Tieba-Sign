<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TiebaApiController extends Controller
{
    const TBS_URL = "http://tieba.baidu.com/dc/common/tbs";
    const SIGN_URL = "http://tieba.baidu.com/c/c/forum/sign";
    const POST_URL = "http://tieba.baidu.com/c/c/post/add";
    const MSIGN_URL = "http://tieba.baidu.com/c/c/forum/msign";
    const ADDTHREAD_URL = "http://tieba.baidu.com/c/c/thread/add";
    const FAV_URL = "http://tieba.baidu.com/c/f/forum/like";
    const LOGIN_URL = "http://c.tieba.baidu.com/c/s/login";
    const PRIVATE_INFO = "http://c.tieba.baidu.com/c/u/user/getPrivateInfo";

    function __construct($BDUSS)
    {
        $this->bduss = $BDUSS;

        $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray([
            'BDUSS' => $this->bduss
        ], 'baidu.com');

        $this->client = new Client([
            // You can set any number of default request options.
            'timeout'  => 10.0,
            'cookies' => $cookieJar,
            'defaults' => [
                'headers' => [
                    'Host' => 'tieba.baidu.com',
                    'Connection' => 'keep-alive',
                    'Cache-Control' => 'max-age=0',
                    'Upgrade-Insecure-Requests' => '1',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept-Language' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7',
                ]
            ]
        ]);
    }

    function getTbs() {
        $response = $this->client->get(self::TBS_URL);
        $response = json_decode($response->getBody());
        return $response->tbs;
    }

    function getToken() {
        $this->client->get("http://www.baidu.com")->getBody();
        $response = $this->client->get("https://passport.baidu.com/v2/api/?getapi&tpl=mn&apiver=v3&class=login&logintype=dialogLogin");
        $data = json_decode(str_replace("'", '"', $response->getBody()));
        return $data->data->token;
    }

    public function getFavForums() {
        $data = array(
            'tbs' => $this->getTbs()
        );

        $response = $this->client->get(self::FAV_URL . "?" . $this->sign($data));
        return json_decode($response->getBody())->forum_list;
    }

    public function tiebaSign($kw){
        $data = array(
            'kw' => $kw,
            'tbs' => $this->getTbs()
        );
        $response = $this->client->get(self::SIGN_URL . "?" . $this->sign($data));
        $response = json_decode($response->getBody());

        return $response;
    }

    public function sign($s){
        ksort($s);
        $a = '';
        $b = '';
        foreach($s as $j=>$i){
            $a .= $j. '=' . $i;
            $b .= $j. '=' . urlencode($i) . '&';
        };
        $a = strtoupper(md5($a . 'tiebaclient!!!'));
        return $b . 'sign=' . $a;
    }

    public function sign_post($s){
        ksort($s);
        $a = '';
        $b = '';
        foreach($s as $j=>$i){
            $a .= $j. '=' . $i;
            $b .= $j. '=' . urlencode($i) . '&';
        };
        $a = strtoupper(md5($a . 'tiebaclient!!!'));
        return $a;
    }

    public function login() {
        $data = array(
            'BDUSS' => $this->bduss,
            'bdusstoken' => $this->getToken(),
        );
        $data["sign"] = $this->sign_post($data);

        $response = $this->client->post(self::LOGIN_URL, ["form_params" => $data]);
        return json_decode($response->getBody());
    }
}
