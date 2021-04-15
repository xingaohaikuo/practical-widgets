<?php

namespace Practical\Tool;

/**
 *             ,%%%%%%%%,
 *           ,%%/\%%%%/\%%
 *          ,%%%\c "" J/%%%
 * %.       %%%%/ o  o \%%%
 * `%%.     %%%%    _  |%%%
 *  `%%     `%%%%(__Y__)%%'
 *  //       ;%%%%`\-/%%%'
 * ((       /  `%%%%%%%'
 *  \\    .'          |
 *   \\  /       \  | |
 *    \\/         ) | |
 *     \         /_ | |__
 *     (___________))))))) 攻城湿
 *
 *        _       _
 * __   _(_)_   _(_) __ _ _ __
 * \ \ / / \ \ / / |/ _` |'_ \
 *  \ V /| |\ V /| | (_| | | | |
 *   \_/ |_| \_/ |_|\__,_|_| |_|
 * 
 * curl请求封装
 */
class CurlHttp
{
    private $ch ;
    private $url ;
    private $method ;
    private $params ;
    private $timeout;
    protected $multipart ;

    public function __construct()
    {
        $this->timeout = 120 ;
    }

    public function Get($data)
    {
        $data['method'] = "GET";
        return $this->performRequest($data);
    }

    public function Post($data)
    {
        $data['method'] = "POST";
        return $this->performRequest($data);
    }

    public function Put($data)
    {
        $data['method'] = "PUT";
        return $this->performRequest($data);
    }

    public function Delete($data)
    {
        $data['method'] = "DELETE";
        return $this->performRequest($data);
    }

    public function Upload($data)
    {
        $data['method'] = "POST";
        $this->multipart = true;
        return $this->performRequest($data);
    }

    /**
     * Http 请求
     * @param $data
     * @return array
     */
    public function performRequest($data)
    {
        $this->ch = curl_init();
        $url = $data['url'];
        try {
            $this->dataValication($data);
        } catch (\Exception $e) {
            return ['code'=>-1, 'msg'=>$e->getMessage()];
        }

        $timeout = isset($data['timeout'])?$data['timeout']:$this->timeout;
        $headers = $this->setHeaders($data);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        if (!empty($headers)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($this->ch, CURLOPT_NOBODY, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->method); //设置请求方式

        if ($this->method=="GET") {
            if(strpos($this->url,'?')){
                $this->url .= http_build_query($this->params);
            }else{
                $this->url .= '?' . http_build_query($this->params);
            }
        }else{
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->multipart?$this->params:http_build_query($this->params));
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->url);

        if (1 == strpos('$'.$this->url, "https://")) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($this->ch);


        if(!curl_errno($this->ch)){
            list($response_header, $response_body) = explode("\r\n\r\n", $result, 2);
            $contentType = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);

            $info = curl_getinfo($this->ch);
            $response = ['code'=>0, 'msg'=>'OK', 'data'=>json_decode($response_body, true), 'contentType'=>$contentType];
        }else{
            $response = ['data'=>['code'=>-1, 'msg'=>"请求 $url 出错: Curl error: ". curl_error($this->ch)]];
        }

        curl_close($this->ch);

        return $response;
    }

    /**
     * 设置Header信息
     * @param $data
     * @return array
     */
    public function setHeaders($data)
    {
        $headers = array();
        if (isset($data['headers'])) {
            foreach ($data['headers'] as $key=>$item) {
                $headers[] = "$key:$item";
            }
        }
        $headers[] = "Expect:"; // post数据大于1k时，默认不需要添加Expect:100-continue

        return $headers;
    }

    public function setTimeout($timeout)
    {
        if (!empty($timeout) || $timeout != 30) {
            $this->timeout = $timeout ;
        }

        return $this;
    }

    /**
     * 数据验证
     * @param $data
     * @throws \Exception
     */
    public function dataValication($data)
    {
        if(!isset($data['url']) || empty($data['url'])){
            throw new \Exception("HttpClient Error: Uri不能为空", 4422);
        }else{
            $this->url = $data['url'];
        }

        if(!isset($data['params']) || empty($data['params'])){
            $this->params = [];
        }else{
            $this->params = $data['params'];
        }

        if(!isset($data['method']) || empty($data['method'])){
            $this->method = "POST";
        }else{
            $this->method = $data['method'];
        }
    }

}