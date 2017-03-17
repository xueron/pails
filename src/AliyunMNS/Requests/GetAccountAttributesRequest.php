<?php
namespace AliyunMNS\Requests;

use AliyunMNS\Requests\BaseRequest;

class GetAccountAttributesRequest extends BaseRequest
{
    public function __construct()
    {
        parent::__construct('get', '/?accountmeta=true');
    }

    public function generateBody()
    {
        return null;
    }

    public function generateQueryString()
    {
        return null;
    }
}
