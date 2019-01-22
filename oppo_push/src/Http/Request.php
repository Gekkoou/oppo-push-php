<?php

namespace Http;

class Request
{
    private $_curl;
    private $_baseUrl;
    private $_httpVersion;

    /**
     * 构造函数。
     *
     * @param string $baseUrl
     */
    public function __construct($baseUrl = '')
    {
        if (is_string($baseUrl))
            $this->_baseUrl = $baseUrl;
    }

    /**
     * 请求的统一接口。
     *
     * @param $method
     * @param $url
     * @param $options
     * @return Response
     * @throws \Exception
     */
    public function request($method, $url, $options)
    {
        $this->_curl = curl_init();
        $this->setUrl($url);
        $this->_setHttpVersionOption();
        $this->setHttpMethodOption($method);
        if (is_array($options) && !empty($options)) {
            self::setOptions($this->_curl, $options);
        }
        return new Response($this->_curl);
    }

    /**
     * POST 请求方法。
     *
     * @param string $url
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function post($url, $options = array())
    {
        return $this->request('post', $url, $options);
    }

    /**
     * GET 请求方法。
     *
     * @param $url
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function get($url, $options = array())
    {
        return $this->request('get', $url, $options);
    }

    /**
     * PUT 请求方法。
     *
     * @param $url
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function put($url, $options = array())
    {
        return $this->request('put', $url, $options);
    }

    /**
     * DELETE 请求方法。
     *
     * @param $url
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function delete($url, $options = array())
    {
        return $this->request('delete', $url, $options);
    }

    /**
     * OPTIONS 请求方法。
     *
     * @param $url
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function options($url, $options = array())
    {
        return $this->request('options', $url, $options);
    }

    /**
     * PATCH 请求方法。
     *
     * @param $url
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function patch($url, $options = array())
    {
        return $this->request('patch', $url, $options);
    }

    /**
     * HEAD 请求方法。
     *
     * @param $url
     * @param array $options
     * @return Response
     * @throws \Exception
     */
    public function head($url, $options = array())
    {
        return $this->request('head', $url, $options);
    }

    /**
     * 设置 BaseUrl 参数。
     *
     * @param string $url
     */
    public function setBaseUrl($url)
    {
        if (is_string($url)) {
            $this->_baseUrl = $url;
        }
    }

    /**
     * 设置 Http Version 参数。
     *
     * @param $httpVersion
     */
    public function setHttpVersion($httpVersion)
    {
        if (is_int($httpVersion)) {
            $this->_httpVersion = $httpVersion;
        }
    }

    /**
     * 设置 http 协议版本。
     */
    private function _setHttpVersionOption()
    {
        $http_versions = array(
            Http::HTTP_VERSION_1_0,
            Http::HTTP_VERSION_1_1,
            Http::HTTP_VERSION_2_0,
            Http::HTTP_VERSION_2TLS,
            Http::HTTP_VERSION_2_PRIOR_KNOWLEDGE
        );

        if (in_array($this->_httpVersion, $http_versions)) {
            /**
             * CURL_HTTP_VERSION_2(_0)
             * CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE
             * CURL_HTTP_VERSION_2TLS
             * 以上三个常量是在 PHP 7.0.7 引入的，分别对应整型值为，3、4、5。
             */
            if ($this->_httpVersion >= 3 && !defined('CURL_HTTP_VERSION_2')) {
                throw new \Exception('HTTP / 2 constant was introduced in PHP 7.0.7!');
            }
            curl_setopt($this->_curl, CURLOPT_HTTP_VERSION, $this->_httpVersion);
        } else {
            curl_setopt($this->_curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
        }
    }

    /**
     * 设置请求的 Method。
     *
     * @param $method
     */
    private function setHttpMethodOption($method)
    {
        $method = strtoupper($method);
        $allow_methods = array('GET', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD');
        if ($method == 'POST') {
            curl_setopt($this->_curl, CURLOPT_POST, true);
            curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, $method);
        } else if (in_array($method, $allow_methods)) {
            curl_setopt($this->_curl, CURLOPT_POST, false);
            curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, $method);
            // 解决 HEAD 请求超时问题。
            if ($method == 'HEAD') {
                curl_setopt($this->_curl, CURLOPT_NOBODY, true);
            }
        }
    }

    /**
     * 给请求设置 url 参数。
     *
     * @param string $url
     */
    private function setUrl($url)
    {
        // 如果 $url 以 http(s):// 开头，则无视 baseUrl。
        if (!preg_match('#^https?://#', $url) && $this->_baseUrl != null && $this->_baseUrl != '') {
            $url = rtrim(trim($this->_baseUrl), '/') . '/' . ltrim(trim($url), '/');
        }
        if (preg_match('#^https://#', $url)) {
            curl_setopt($this->_curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($this->_curl, CURLOPT_URL, $url);
    }

    /**
     * 设置请求附带参数。
     *
     * @param $curl
     * @param array $options
     */
    private function setOptions(&$curl, $options)
    {
        $header = array();
        foreach ($options as $key => $item) {
            switch ($key) {
                case 'timeout':
                    if (is_int($item)) {
                        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $item);
                    }
                    break;
                case 'headers':
                    if (is_array($item)) {
                        foreach ($item as $k => $v) {
                            array_push($header, $k . ': ' . $v);
                        }
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                    }
                    break;
                case 'query':
                    if (is_array($item)) {
                        $url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
                        $url .= '?' . http_build_query($options['query']);
                        curl_setopt($curl, CURLOPT_URL, $url);
                    }
                    break;
                case 'data':
                    $fields = is_array($item) ? http_build_query($item) : $item;
                    array_push($header, 'Content-Length: ' . strlen($fields));
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
            }
        }
    }
}