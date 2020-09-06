<?php

namespace hasyan\wechat;

use Curl\Curl;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * User: Lysoft
 * Date: 2017/5/26
 * Time: 9:55
 */
class Wechat
{
    public $errMsg = 0;
    public $errCode;
    public $appId;
    public $appSecret;
    public $mchId;
    public $apiKey;
    public $certPem;
    public $keyPem;
    public $pay;
    public $jsapi;
    public $tplMsg;
    public $cachePath;
    public $cache;
    public $curl;
    public function __construct($args = [])
    {
        $this->appId = isset($args['appId']) ? $args['appId'] : null;
        $this->appSecret = isset($args['appSecret']) ? $args['appSecret'] : null;
        $this->mchId = isset($args['mchId']) ? $args['mchId'] : null;
        $this->apiKey = isset($args['apiKey']) ? $args['apiKey'] : null;
        $this->certPem = isset($args['certPem']) ? $args['certPem'] : null;
        $this->keyPem = isset($args['keyPem']) ? $args['keyPem'] : null;
        $this->cachePath = isset($args['cachePath']) ? $args['cachePath'] : null;
        return $this->init();
    }
    private function init()
    {
        if (!$this->cachePath)
            $this->cachePath = dirname(__DIR__) . '/runtime/cache';
        $this->cache = new FilesystemCache($this->cachePath);
        $this->curl = new Curl();
        $this->curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);

        if ($this->certPem) {
            $this->curl->setOpt(CURLOPT_SSLCERTTYPE, 'PEM');
            $this->curl->setOpt(CURLOPT_SSLCERT, $this->certPem);
        }
        if ($this->keyPem) {
            $this->curl->setOpt(CURLOPT_SSLCERTTYPE, 'PEM');
            $this->curl->setOpt(CURLOPT_SSLKEY, $this->keyPem);
        }

        $this->pay = new Pay($this);
        $this->jsapi = new Jsapi($this);
        $this->tplMsg = new TplMsg($this);
        return $this;
    }
    public function getAccessToken($refresh = false, $expires = 3600)
    {
        $cacheKey = md5("{$this->appId}@access_token");
        $accessToken = $this->cache->fetch($cacheKey);
        $accessTokenOk = $this->checkAccessToken($accessToken);
        if (!$accessToken || $refresh || !$accessTokenOk) {
            $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
            $this->curl->get($api);
            $res = json_decode($this->curl->response, true);
            if (empty($res['access_token'])) {
                $this->errCode = isset($res['errcode']) ? $res['errcode'] : null;
                $this->errMsg = isset($res['errmsg']) ? $res['errmsg'] : null;
                return false;
            }
            $accessToken = $res['access_token'];
            $this->cache->save($cacheKey, $accessToken, $expires);
            return $accessToken;
        } else {
            return $accessToken;
        }
    }
    private function checkAccessToken($accessToken)
    {
        if (!$accessToken)
            return false;
        $api = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token={$accessToken}";
        $this->curl->get($api);
        $res = json_decode($this->curl->response, true);
        if (!empty($res['errcode']) && $res['errcode'] != 1)
            return false;
        return true;
    }


}