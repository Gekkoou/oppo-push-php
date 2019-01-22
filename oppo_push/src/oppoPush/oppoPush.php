<?php
namespace oppoPush;
use Http\Http;
use Http\Request;
use Http\Response;

class oppoPush
{
    private $_authTokenInfo;
    private $_clientKey;
    private $_clientMasterSecret;
    private $_http;
    private $auth_token;
    private $title;
    private $sub_title;
    private $content;
    private $click_action_type;
    private $click_action_activity;
    private $click_action_url;
    private $action_parameters;
    private $intent;
    private $registration_id;

    private $auth_url = 'https://api.push.oppomobile.com/server/v1/auth';
    private $save_message_content_url = 'https://api.push.oppomobile.com/server/v1/message/notification/save_message_content';
    private $broadcast_url = 'https://api.push.oppomobile.com/server/v1/message/notification/broadcast';
    private $unicast_batch_url = 'https://api.push.oppomobile.com/server/v1/message/notification/unicast_batch';

    public function __construct($client_key, $client_master_secret)
    {
        $this->_clientKey = $client_key;
        $this->_clientMasterSecret = $client_master_secret;
        $this->_http = new Request();
        $this->_http->setHttpVersion(Http::HTTP_VERSION_1_1);
    }

    private function getAuthTokenInfo()
    {
        list($msec, $sec) = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $sign = hash('sha256', $this->_clientKey.$timestamp.$this->_clientMasterSecret);
        
        $response = $this->_http->post($this->auth_url, array(
            'data' => array(
                'app_key' => $this->_clientKey,
                'sign' => $sign,
                'timestamp' => $timestamp
            )
        ));
        $res = $response->getResponseArray();
        $this->_authTokenInfo = $res;
        return $this->_authTokenInfo;
    }

    public function getAuthToken()
    {
        if(!$this->_authTokenInfo){
            $this->_authTokenInfo = $this->getAuthTokenInfo();
        }
        $auth_token = '';
        if(isset($this->_authTokenInfo['code']) && $this->_authTokenInfo['code'] == 0){
            $auth_token = $this->_authTokenInfo['data']['auth_token'];
        }
        if(!$auth_token){
            throw  new \Exception("获取 auth_token 失败");
            return null;
        }
        return $auth_token;
    }

    public function getAuthTokenExpiresTime()
    {
        if(!$this->_authTokenInfo){
            $this->_authTokenInfo = $this->getAuthTokenInfo();
        }
        $auth_token = '';
        if(isset($this->_authTokenInfo['code']) && $this->_authTokenInfo['code'] == 0){
            $expires_time = floor($this->_authTokenInfo['data']['create_time'] / 1000) + 86400;
        }
        return $expires_time;
    }

    public function setTitle($title = '')
    {
        $this->title=$title;
        return $this;
    }

    public function setSubTitle($sub_title = '')
    {
        $this->sub_title = $sub_title;
        return $this;
    }

    public function setContent($content = '')
    {
        $this->content = $content;
        return $this;
    }

    public function setIntent($click_action_activity = '')
    {
        $this->click_action_activity = $click_action_activity;
        return $this;
    }

    public function setActionUrl($click_action_url = '')
    {
        $this->click_action_url = $click_action_url;
        return $this;
    }

    public function setActionParameters($action_parameters = array())
    {
        $this->action_parameters = $action_parameters;
        return $this;
    }

    public function setAuthToken($auth_token="")
    {
        $this->auth_token = $auth_token;
        return $this;
    }

    public function addRegistrationId($registration_id = '')
    {
        $this->registration_id[] = $registration_id;
        return $this;
    }

    private function check()
    {
        $_clientKey = trim($this->_clientKey);
        $_clientMasterSecret = trim($this->_clientMasterSecret);
        $title = trim($this->title);
        $sub_title = trim($this->sub_title);
        $content = trim($this->content);
        $click_action_activity = trim($this->click_action_activity);
        $click_action_url = trim($this->click_action_url);
        $action_parameters = trim($this->action_parameters);
        $auth_token = trim($this->auth_token);

        $registration_id = $this->registration_id;
        if(!empty($registration_id) && is_array($registration_id)){
            foreach ($registration_id as $key => $val) {
                $registration_id[$key] = trim($val);
            }
            array_filter($registration_id);
        }

        if(empty($_clientKey)){
            throw new \Exception("必须设置 clientKey");
        }
        if(empty($_clientMasterSecret)){
            throw new \Exception("必须设置 clientMasterSecret");
        }
        if(empty($title)){
            throw new \Exception("必须设置 title");
        }
        if(empty($content)){
            throw new \Exception("必须设置 content");
        }
        if(empty($auth_token)){
            throw new \Exception("必须设置 auth_token");
        }
        if(!empty($click_action_activity) && !empty($click_action_url)){
            throw new \Exception("setIntent 和 setActionUrl 只能设置一个");
        }
        
        $this->title = $title;
        $this->sub_title = $sub_title;
        $this->content = $content;
        $this->click_action_activity = $click_action_activity;
        $this->click_action_url = $click_action_url;
        $this->action_parameters = $action_parameters;
        $this->auth_token = $auth_token;
        $this->registration_id = $registration_id;
    }

    private function build()
    {
        $data = array(
            'title' => $this->title,
            'content' => $this->content,
            'sub_title' => $this->sub_title,
            'click_action_type' => 0,
        );
        
        if(!empty($this->click_action_activity)){
            $data['click_action_type'] = 1;
            $data['click_action_activity'] = $this->click_action_activity;
        }
        if(!empty($this->click_action_url)){
            $data['click_action_type'] = 2;
            $data['click_action_url'] = $this->click_action_url;
        }
        
        if(is_array($this->action_parameters) && count($this->action_parameters) > 0){
            $data['action_parameters'] = json_encode($this->action_parameters);
        }elseif(is_string($this->action_parameters) && trim($this->action_parameters) != ''){
            $data['action_parameters'] = $this->action_parameters;
        }
        
        return $data;
    }

    private function save_message_content()
    {
        $this->check();
        $data = $this->build();
        $data['auth_token'] = $this->auth_token;
        
        $response = $this->_http->post($this->save_message_content_url, array(
            'data' => $data
        ));
        $msg_data = $response->getResponseArray();
        
        return $msg_data;
    }

    private function broadcast($target_type = 1)
    {
        $msg_data = $this->save_message_content();
        if(empty($msg_data) || $msg_data['code'] != 0){
            return $msg_data;
        }
        $message_id = $msg_data['data']['message_id'];
        
        $data = array(
            'auth_token' => $this->auth_token,
            'message_id' => $message_id,
            'target_type' => 1,
        );
        
        if($target_type == 2 && !empty($this->registration_id)){
            $data['target_type'] = 2;
            $data['target_value'] = implode(',', $this->registration_id);
        }
        
        $response = $this->_http->post($this->broadcast_url, array(
            'data' => $data
        ));
        
        $res = $response->getResponseArray();
        return $res;
    }

    public function broadcastAll()
    {
        return $this->broadcast(1);
    }
    
    public function broadcastByRegId()
    {
        if(empty($this->registration_id)){
            throw new \Exception("必须设置 registration_id");
        }
        return $this->broadcast(2);
    }

    // 弃用别名推送
    public function sendToAliases($alias = array())
    {
        if(empty($alias) && is_array($alias)){
            throw new \Exception("必须设置 alias");
        }
        
        $data = $alias_list = array();
        foreach ($alias as $key => $val) {
            $alias_list[] = trim($val);
        }
        array_filter($alias_list);
        
        $this->check();
        $notification = $this->build();
        
        foreach($alias_list as $key => $val){
            $data[] = array(
                'target_type' => 3,
                'target_value' => $val,
                'notification' => $notification,
            );
        }
        
        $messages = json_encode($data);
        
        $response = $this->_http->post($this->unicast_batch_url, array(
            'data' => array(
                'messages' => $messages,
                'auth_token' => $this->auth_token,
            )
        ));
        
        $res = $response->getResponseArray();
        return $res;
    }
}