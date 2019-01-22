# oppo 推送 simple demo

只有普通的 全量用户推送 与 registration_id 推送  `(别名推送官方屏蔽不许用, 没有离线与定时推送等高级设置功能~)`

参考文档: http://storepic.oppomobile.com/openplat/resource/201812/03/OPPO推送平台服务端API-V1.3.pdf

~`官方没有提供php版本SKD, 某度某歌搜不到, 无奈敲了些烂代码, 大牛轻喷`~

----

```php
include_once(dirname(__FILE__).'/oppo_push/autoload.php');

$client = new \oppoPush\oppoPush({your AppKey}, {your MasterSecret}); // AppKey 与 MasterSecret(非 AppSecret)
$authToken = $client->getAuthToken(); // 有效期24小时
$client->setTitle($title)
       ->setContent($message)
       ->setAuthToken($authToken);
$client->broadcastAll(); // 全量用户推送

// $client->getAuthTokenExpiresTime();           // 获取 auth_token 过期时间
// $client->ssetIntent('xxx.xxx.xxx');           // 打开应用内页的 intent action
// $client->setActionUrl('http://www.xxx.com');  // 打开网页
// $client->setActionParameters({Parameters});   // 打开应用内页或网页时传递的参数 (数组或json类型)
// $client->addRegistrationId('xxx');            // 添加需要发送设备的 registration_id, 最多 1000 个
// $client->broadcastByRegId();                  // registration_id 推送
```
