
<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class ContificoCurl
{
    // The HTTP authentication method(s) to use.

    /**
     * @var string Type AUTH_BASIC
     */
    const AUTH_BASIC = CURLAUTH_BASIC;

    /**
     * @var string Type AUTH_DIGEST
     */
    const AUTH_DIGEST = CURLAUTH_DIGEST;

    /**
     * @var string Type AUTH_GSSNEGOTIATE
     */
    const AUTH_GSSNEGOTIATE = CURLAUTH_GSSNEGOTIATE;

    /**
     * @var string Type AUTH_NTLM
     */
    const AUTH_NTLM = CURLAUTH_NTLM;

    /**
     * @var string Type AUTH_ANY
     */
    const AUTH_ANY = CURLAUTH_ANY;

    /**
     * @var string Type AUTH_ANYSAFE
     */
    const AUTH_ANYSAFE = CURLAUTH_ANYSAFE;

    /**
     * @var string The user agent name which is set when making a request
     */
    const USER_AGENT = 'PHP Curl/1.9 (+https://github.com/php-mod/curl)';

    private $cookies = array();

    private $headers = array();

    /**
     * @var resource Contains the curl resource created by `curl_init()` function
     */
    public $curl;

    /**
     * @var bool Whether an error occured or not
     */
    public $error = false;

    /**
     * @var int Contains the error code of the curren request, 0 means no error happend
     */
    public $error_code = 0;

    /**
     * @var string If the curl request failed, the error message is contained
     */
    public $error_message = null;

    /**
     * @var bool Whether an error occured or not
     */
    public $curl_error = false;

    /**
     * @var int Contains the error code of the curren request, 0 means no error happend.
     * @see https://curl.haxx.se/libcurl/c/libcurl-errors.html
     */
    public $curl_error_code = 0;

    /**
     * @var string If the curl request failed, the error message is contained
     */
    public $curl_error_message = null;

    /**
     * @var bool Whether an error occured or not
     */
    public $http_error = false;

    /**
     * @var int Contains the status code of the current processed request.
     */
    public $http_status_code = 0;

    /**
     * @var string If the curl request failed, the error message is contained
     */
    public $http_error_message = null;

    /**
     * @var string|array TBD (ensure type) Contains the request header informations
     */
    public $request_headers = null;

    /**
     * @var string|array TBD (ensure type) Contains the response header informations
     */
    public $response_headers = array();

    /**
     * @var string Contains the response from the curl request
     */
    public $response = null;

    /**
     * @var bool Whether the current section of response headers is after 'HTTP/1.1 100 Continue'
     */
    protected $response_header_continue = false;

    /**
     * Constructor ensures the available curl extension is loaded.
     *
     * @throws \ErrorException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException(
                'The cURL extensions is not loaded'
            );
        }

        $this->init();
    }

    // private methods

    /**
     * Initializer for the curl resource.
     *
     * Is called by the __construct() of the class or when the curl request is reseted.
     * @return self
     */
    private function init()
    {
        $this->curl = curl_init();
        $this->setUserAgent(self::USER_AGENT);
        $this->setOpt(CURLINFO_HEADER_OUT, true);
        $this->setOpt(CURLOPT_HEADER, false);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLOPT_HEADERFUNCTION, array($this, 'addResponseHeaderLine'));
        return $this;
    }

    /**
     * Handle writing the response headers
     *
     * @param resource $curl The current curl resource
     * @param string $header_line A line from the list of response headers
     *
     * @return int Returns the length of the $header_line
     */
    public function addResponseHeaderLine($curl, $header_line)
    {
        if (empty($curl)) {
            return false;
        }
        $trimmed_header = trim($header_line, "\r\n");

        if ($trimmed_header === "") {
            $this->response_header_continue = false;
        } elseif (Tools::strtolower($trimmed_header) === 'http/1.1 100 continue') {
            $this->response_header_continue = true;
        } elseif (!$this->response_header_continue) {
            $this->response_headers[] = $trimmed_header;
        }

        return Tools::strlen($header_line);
    }

    // protected methods

    /**
     * Execute the curl request based on the respectiv settings.
     *
     * @return int Returns the error code for the current curl request
     */
    protected function exec()
    {
        $this->response_headers = array();
        $this->response = curl_exec($this->curl);
        $this->curl_error_code = curl_errno($this->curl);
        $this->curl_error_message = curl_error($this->curl);
        $this->curl_error = !($this->curl_error_code === 0);
        $this->http_status_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $this->http_error = in_array(floor($this->http_status_code / 100), array(4, 5));
        $this->error = $this->curl_error || $this->http_error;
        $this->error_code = $this->error ? ($this->curl_error ? $this->curl_error_code : $this->http_status_code) : 0;
        $this->request_headers =
            preg_split('/\r\n/', curl_getinfo($this->curl, CURLINFO_HEADER_OUT), null, PREG_SPLIT_NO_EMPTY);
        $this->http_error_message =
            $this->error ? (isset($this->response_headers['0']) ? $this->response_headers['0'] : '') : '';
        $this->error_message = $this->curl_error ? $this->curl_error_message : $this->http_error_message;

        return $this->error_code;
    }

    /**
     * @param array|object|string $data
     */
    protected function preparePayload($data)
    {
        $this->setOpt(CURLOPT_POST, true);

        if (is_array($data) || is_object($data)) {
            $data = http_build_query($data);
        }

        $this->setOpt(CURLOPT_POSTFIELDS, $data);
    }

    /**
     * Set auth options for the current request.
     *
     * Available auth types are:
     *
     * + self::AUTH_BASIC
     * + self::AUTH_DIGEST
     * + self::AUTH_GSSNEGOTIATE
     * + self::AUTH_NTLM
     * + self::AUTH_ANY
     * + self::AUTH_ANYSAFE
     *
     * @param int $httpauth The type of authentication
     */
    protected function setHttpAuth($httpauth)
    {
        $this->setOpt(CURLOPT_HTTPAUTH, $httpauth);
    }

    // public methods

    /**
     * @deprecated calling exec() directly is discouraged
     */
    public function mainExec()
    {
        return $this->exec();
    }

    // functions

    /**
     * Make a get request with optional data.
     *
     * The get request has no body data, the data will be correctly added to the $url with the http_build_query().
     *
     * @param string $url  The url to make the get request for
     * @param array  $data Optional arguments who are part of the url
     * @return self
     */
    public function get($url, $data = array())
    {
        if (count($data) > 0) {
            $this->setOpt(CURLOPT_URL, $url . '?' . http_build_query($data));
        } else {
            $this->setOpt(CURLOPT_URL, $url);
        }
        $this->setOpt(CURLOPT_HTTPGET, true);
        $this->exec();
        return $this;
    }

    /**
     * Make a post request with optional post data.
     *
     * @param string $url  The url to make the post request
     * @param array  $data Post data to pass to the url
     * @return self
     */
    public function post($url, $data = array())
    {
        $this->setOpt(CURLOPT_URL, $url);
        $this->preparePayload($data);
        $this->exec();
        return $this;
    }

    /**
     * Make a put request with optional data.
     *
     * The put request data can be either sent via payload or as get paramters of the string.
     *
     * @param string $url The url to make the put request
     * @param array $data Optional data to pass to the $url
     * @param bool $payload Whether the data should be transmitted trough payload or as get parameters of the string
     * @return self
     */
    public function put($url, $data = array(), $payload = false)
    {
        if (!empty($data)) {
            if ($payload === false) {
                $url .= '?' . http_build_query($data);
            } else {
                $this->preparePayload($data);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $this->exec();
        return $this;
    }

    /**
     * Make a patch request with optional data.
     *
     * The patch request data can be either sent via payload or as get paramters of the string.
     *
     * @param string $url The url to make the patch request
     * @param array $data Optional data to pass to the $url
     * @param bool $payload Whether the data should be transmitted trough payload or as get parameters of the string
     * @return self
     */
    public function patch($url, $data = array(), $payload = false)
    {
        if (!empty($data)) {
            if ($payload === false) {
                $url .= '?' . http_build_query($data);
            } else {
                $this->preparePayload($data);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->exec();
        return $this;
    }

    /**
     * Make a delete request with optional data.
     *
     * @param string $url The url to make the delete request
     * @param array $data Optional data to pass to the $url
     * @param bool $payload Whether the data should be transmitted trough payload or as get parameters of the string
     * @return self
     */
    public function delete($url, $data = array(), $payload = false)
    {
        if (!empty($data)) {
            if ($payload === false) {
                $url .= '?' . http_build_query($data);
            } else {
                $this->preparePayload($data);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->exec();
        return $this;
    }

    // setters

    /**
     * Pass basic auth data.
     *
     * If the rquested url is secured by an httaccess basic auth mechanism
     * you can use this method to provided the auth data.
     *
     * ```php
     * $curl = new Curl();
     * $curl->setBasicAuthentication('john', 'doe');
     * $curl->get('http://example.com/secure.php');
     * ```
     *
     * @param string $username The username for the authentification
     * @param string $password The password for the given username for the authentification
     * @return self
     */
    public function setBasicAuthentication($username, $password)
    {
        $this->setHttpAuth(self::AUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    /**
     * Provide optional header informations.
     *
     * In order to pass optional headers by key value pairing:
     *
     * ```php
     * $curl = new Curl();
     * $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
     * $curl->get('http://example.com/request.php');
     * ```
     *
     * @param string $key   The header key
     * @param string $value The value for the given header key
     * @return self
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $key . ': ' . $value;
        $this->setOpt(CURLOPT_HTTPHEADER, array_values($this->headers));
        return $this;
    }

    /**
     * Provide a User Agent.
     *
     * In order to provide you cusomtized user agent name you can use this method.
     *
     * ```php
     * $curl = new Curl();
     * $curl->setUserAgent('My John Doe Agent 1.0');
     * $curl->get('http://example.com/request.php');
     * ```
     *
     * @param string $useragent The name of the user agent to set for the current request
     * @return self
     */
    public function setUserAgent($useragent)
    {
        $this->setOpt(CURLOPT_USERAGENT, $useragent);
        return $this;
    }

    /**
     * @deprecated Call setReferer() instead
     */
    public function setReferrer($referrer)
    {
        $this->setReferer($referrer);
        return $this;
    }

    /**
     * Set the HTTP referer header.
     *
     * The $referer informations can help identify the requested client where the requested was made.
     *
     * @param string $referer An url to pass and will be set as referer header
     * @return self
     */
    public function setReferer($referer)
    {
        $this->setOpt(CURLOPT_REFERER, $referer);
        return $this;
    }

    /**
     * Set contents of HTTP Cookie header.
     *
     * @param string $key   The name of the cookie
     * @param string $value The value for the provided cookie name
     * @return self
     */
    public function setCookie($key, $value)
    {
        $this->cookies[$key] = $value;
        $this->setOpt(CURLOPT_COOKIE, http_build_query($this->cookies, '', '; '));
        return $this;
    }

    /**
     * Set customized curl options.
     *
     * To see a full list of options: http://php.net/curl_setopt
     *
     * @see http://php.net/curl_setopt
     *
     * @param int   $option The curl option constante e.g. `CURLOPT_AUTOREFERER`, `CURLOPT_COOKIESESSION`
     * @param mixed $value  The value to pass for the given $option
     */
    public function setOpt($option, $value)
    {
        return curl_setopt($this->curl, $option, $value);
    }

    /**
     * Get customized curl options.
     *
     * To see a full list of options: http://php.net/curl_getinfo
     *
     * @see http://php.net/curl_getinfo
     *
     * @param int   $option The curl option constante e.g. `CURLOPT_AUTOREFERER`, `CURLOPT_COOKIESESSION`
     * @param mixed $value  The value to check for the given $option
     */
    public function getOpt($option)
    {
        return curl_getinfo($this->curl, $option);
    }

    /**
     * Return the endpoint set for curl
     *
     * @see http://php.net/curl_getinfo
     *
     * @return string of endpoint
     */
    public function getEndpoint()
    {
        return $this->getOpt(CURLINFO_EFFECTIVE_URL);
    }

    /**
     * Enable verbositiy.
     *
     * @todo As to keep naming convention it should be renamed to `setVerbose()`
     *
     * @param string $on
     * @return self
     */
    public function verbose($on = true)
    {
        $this->setOpt(CURLOPT_VERBOSE, $on);
        return $this;
    }

    /**
     * Reset all curl options.
     *
     * In order to make multiple requests with the same curl object all settings requires to be reset.
     * @return self
     */
    public function reset()
    {
        $this->close();
        $this->cookies = array();
        $this->headers = array();
        $this->error = false;
        $this->error_code = 0;
        $this->error_message = null;
        $this->curl_error = false;
        $this->curl_error_code = 0;
        $this->curl_error_message = null;
        $this->http_error = false;
        $this->http_status_code = 0;
        $this->http_error_message = null;
        $this->request_headers = null;
        $this->response_headers = array();
        $this->response = null;
        $this->init();
        return $this;
    }

    /**
     * Closing the current open curl resource.
     * @return self
     */
    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        return $this;
    }

    /**
     * Close the connection when the Curl object will be destroyed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Was an 'info' header returned.
     * @return bool
     */
    public function isInfo()
    {
        return $this->http_status_code >= 100 && $this->http_status_code < 200;
    }

    /**
     * Was an 'OK' response returned.
     * @return bool
     */
    public function isSuccess()
    {
        return $this->http_status_code >= 200 && $this->http_status_code < 300;
    }

    /**
     * Was a 'redirect' returned.
     * @return bool
     */
    public function isRedirect()
    {
        return $this->http_status_code >= 300 && $this->http_status_code < 400;
    }

    /**
     * Was an 'error' returned (client error or server error).
     * @return bool
     */
    public function isError()
    {
        return $this->http_status_code >= 400 && $this->http_status_code < 600;
    }

    /**
     * Was a 'client error' returned.
     * @return bool
     */
    public function isClientError()
    {
        return $this->http_status_code >= 400 && $this->http_status_code < 500;
    }

    /**
     * Was a 'server error' returned.
     * @return bool
     */
    public function isServerError()
    {
        return $this->http_status_code >= 500 && $this->http_status_code < 600;
    }

    /**
     * Get a specific response header key or all values from the response headers array.
     *
     * Usage example:
     *
     * ```php
     * $curl = (new Curl())->get('http://example.com');
     *
     * echo $curl->getResponseHeaders('Content-Type');
     * ```
     *
     * Or in order to dump all keys with the given values use:
     *
     * ```php
     * $curl = (new Curl())->get('http://example.com');
     *
     * var_dump($curl->getResponseHeaders());
     * ```
     *
     * @param string $headerKey Optional key to get from the array.
     * @return bool|string
     * @since 1.9
     */
    public function getResponseHeaders($headerKey = null)
    {
        $headers = array();
        $headerKey =  Tools::strtolower($headerKey);

        foreach ($this->response_headers as $header) {
            $parts = explode(":", $header, 2);

            $key = isset($parts[0]) ? $parts[0] : null;
            $value = isset($parts[1]) ? $parts[1] : null;

            $headers[trim(Tools::strtolower($key))] = trim($value);
        }

        if ($headerKey) {
            return isset($headers[$headerKey]) ? $headers[$headerKey] : false;
        }

        return $headers;
    }
}
