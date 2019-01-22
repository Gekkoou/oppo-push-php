<?php

namespace Http;

/**
 * @method static void setBaseUrl(string $url)
 * @method static Response get(string $url, array $options = null)
 * @method static Response put(string $url, array $options = null)
 * @method static Response post(string $url, array $options = null)
 * @method static Response head(string $url, array $options = null)
 * @method static Response patch(string $url, array $options = null)
 * @method static Response delete(string $url, array $options = null)
 * @method static Response options(string $url, array $options = null)
 */
class Http
{
    const HTTP_VERSION_1_0 = 1;
    const HTTP_VERSION_1_1 = 2;
    const HTTP_VERSION_2_0 = 3;
    const HTTP_VERSION_2TLS = 4;
    const HTTP_VERSION_2_PRIOR_KNOWLEDGE = 5;

    private static $_sRequest;
    private static $_sAllowMethods = array(
        'get', 'post', 'put', 'delete', 'patch', 'options', 'head', 'setBaseUrl'
    );

    public static function __callStatic($name, $arguments)
    {
        if (self::$_sRequest == null)
            self::$_sRequest = new Request();
        if (in_array($name, self::$_sAllowMethods) && method_exists(self::$_sRequest, $name)) {
            $response = call_user_func_array(array(self::$_sRequest, $name), $arguments);
            return $response;
        } else {
            throw new \Exception('Can not found method:' . $name);
        }
    }
}