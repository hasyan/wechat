<?php
/**
 * User: Lysoft
 * Date: 2017/5/26
 * Time: 11:32
 */
namespace hasyan\wechat;
abstract class Base
{
    protected $wechat;
    public function __construct($wechat)
    {
        $this->wechat = $wechat;
    }
}