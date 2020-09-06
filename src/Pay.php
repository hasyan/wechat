<?php
/**
 * User: Lysoft
 * Date: 2017/5/26
 * Time: 10:18
 */

namespace hasyan\wechat;


class Pay extends Base
{
    public function unifiedOrder($args)
    {
        $args['appid'] = $this->wechat->appId;
        $args['mch_id'] = $this->wechat->mchId;
        $args['nonce_str'] = md5(uniqid());
        $args['sign_type'] = 'MD5';
        $args['spbill_create_ip'] = '127.0.0.1';
        $args['sign'] = $this->makeSign($args);
        $xml = DataTransform::arrayToXml($args);
        $api = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $this->wechat->curl->post($api, $xml);
        if (!$this->wechat->curl->response)
            return false;
        return DataTransform::xmlToArray($this->wechat->curl->response);
    }
    public function orderQuery($order_no)
    {
        $data = [
            'appid' => $this->wechat->appId,
            'mch_id' => $this->wechat->mchId,
            'out_trade_no' => $order_no,
            'nonce_str' => md5(uniqid()),
        ];
        $data['sign'] = $this->makeSign($data);
        $xml = DataTransform::arrayToXml($data);
        $api = "https://api.mch.weixin.qq.com/pay/orderquery";
        $this->wechat->curl->post($api, $xml);
        if (!$this->wechat->curl->response)
            return false;
        return DataTransform::xmlToArray($this->wechat->curl->response);
    }
    public function getJsSignPackage($args)
    {
    }
    public function getAppSignPackage($args)
    {
    }
    public function refund($args)
    {
        $args['appid'] = $this->wechat->appId;
        $args['mch_id'] = $this->wechat->mchId;
        $args['nonce_str'] = md5(uniqid());
        $args['op_user_id'] = $this->wechat->mchId;
        $args['sign'] = $this->makeSign($args);
        $xml = DataTransform::arrayToXml($args);
        $api = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        $this->wechat->curl->post($api, $xml);
        if (!$this->wechat->curl->response)
            return false;
        return DataTransform::xmlToArray($this->wechat->curl->response);
    }
    public function transfers($args)
    {
        $args['mch_appid'] = $this->wechat->appId;
        $args['mchid'] = $this->wechat->mchId;
        $args['nonce_str'] = md5(uniqid());
        $args['check_name'] = 'NO_CHECK';
        $args['spbill_create_ip'] = '127.0.0.1';
        $args['sign'] = $this->makeSign($args);
        $xml = DataTransform::arrayToXml($args);
        $api = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $this->wechat->curl->post($api, $xml);
        if (!$this->wechat->curl->response)
            return false;
        return DataTransform::xmlToArray($this->wechat->curl->response);
    }
    public function sendRedPack($args)
    {
    }
    public function sendGroupRedPack($args)
    {
    }
    public function makeSign($args)
    {
        if (isset($args['sign']))
            unset($args['sign']);
        ksort($args);
        foreach ($args as $i => $arg) {
            if ($args === null || $arg === '')
                unset($args[$i]);
        }
        $string = DataTransform::arrayToUrlParam($args, false);
        $string = $string . "&key={$this->wechat->apiKey}";
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }

}