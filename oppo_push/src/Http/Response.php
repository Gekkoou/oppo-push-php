<?php

namespace Http;
class Response
{
    private $_statusCode;
    private $_content;
    private $_url;

    public function __construct($ch)
    {
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->_content = curl_exec($ch);
        $this->_statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    public function getResponseText()
    {
        return $this->_content;
    }

    public function getResponseArray()
    {
        return json_decode($this->_content, true);
    }

    public function getResponseObject()
    {
        return json_decode($this->_content);
    }
}