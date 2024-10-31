<?php
namespace pushworld\api;

use Exception;
use pushworld\api\Exception\PushWorldApiGetTokenException;
use pushworld\api\Exception\PushWorldApiForbbidenException;
use pushworld\api\Exception\PushWorldApiValidationException;
use pushworld\api\Exception\PushWorldApiSaveAccessTokenException;


class PushWorldApi implements PushWorldApiInterface
{
    private $version = 'v2';
    private $apiUrl = 'https://api.push.world/';

    private $clientId = '';
    private $clientSecret = '';

    private $token = '';

    public $filesPath = '';

    protected $requestCount = 0;
    protected $maxRequestCount = 3;

    /**
     * Form and send request to API service
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $filesPath
     * @throws Exception
     */
    public function __construct($clientId, $clientSecret, $filesPath = '')
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception('Empty Client ID or Client Secret');
        }

        if (empty($filesPath)) {
            $filesPath = sys_get_temp_dir() . '/';
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->filesPath = $filesPath;

        $hash = md5($clientId . $clientSecret);

        if (file_exists($this->filesPath . $hash)) {
            $this->token = file_get_contents($this->filesPath . $hash);
        } else {
            $this->getAccessToken();
        }
    }

    /**
     * Form and send request to API service
     *
     * @param $path
     * @param string $method
     * @param array $data
     * @param bool $useToken
     * @throws PushWorldApiForbbidenException
     * @return stdClass
     */
    private function sendRequest($path, $method = 'GET', $data = array(), $useToken = true)
    {
        $url = $this->apiUrl . $this->version . '/' . $path;

        $method = strtoupper($method);
        $curl = curl_init();

        if ($useToken && !empty($this->token)) {
            $headers = array('Authorization: Bearer ' . $this->token);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, count($data));
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            default:
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                }
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $response = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseBody = substr($response, $header_size);
        $error = curl_error($curl);

        curl_close($curl);

        $body = json_decode($responseBody);

        switch ($headerCode) {
            case 403:
                $this->requestCount = 0;
                throw new PushWorldApiForbbidenException($body->message);
                break;
            case 422:
                $this->requestCount = 0;
                throw new PushWorldApiValidationException($body->message);
                break;
            case 401:
                if ($this->requestCount >= $this->maxRequestCount) {
                    throw new PushWorldApiForbbidenException('Maximum connection attempts reached. Last error mesage is \"' . $body->message . '\"');
                }

                $this->requestCount++;

                if ($this->getAccessToken()) {
                    return $this->sendRequest($path, $method, $data, $useToken);
                }
                break;
            default:
                if ($useToken) {
                    $this->requestCount = 0;
                }
                return $body;
        }
    }

    /**
     * Get access token
     *
     * @throws PushWorldApiGetTokenException
     * @throws PushWorldApiSaveAccessTokenException
     * @return bool
     */
    private function getAccessToken()
    {
        $data = array();
        $data['client_id'] = $this->clientId;
        $data['client_secret'] = $this->clientSecret;
        $data['grant_type'] = 'client_credentials';

        $requestResult = $this->sendRequest('oauth/access_token', 'POST', $data, false);

        if (!isset($requestResult->access_token)) {
            throw new PushWorldApiGetTokenException($requestResult->message);
        }

        $this->token = $requestResult->access_token;

        $hash = md5($this->clientId . $this->clientSecret);

        $file = fopen($this->filesPath . $hash, "w");

        if (!$file) {
            throw new PushWorldApiSaveAccessTokenException('Unable to save access token to file :: ' . $this->filesPath . $hash);
        }

        fwrite($file, $this->token);
        fclose($file);

        return true;
    }

    /*
    * API interface implementation
    */

    /**
    * Create new push message
    *
    * @param $platformCode
    * @param array $multicast
    *    $multicast = [
    *      'title'         => (string) Mandatory, notification title.
    *      'text'          => (string) Mandatory, notification text.
    *      'url'           => (string) Mandatory, the URL on which the transition occurs, by clicking on the text of the notification.
    *      'image'         => (string) Path to the image, if not specified - the default image specified at the platform creation is used.
    *      'action1_title' => (string) first button text.
    *      'action1_url'   => (string) first button URL.
    *      'action2_title' => (string) second button text.
    *      'action2_url'   => (string) second button URL.
    *      'duration'      => (int)    Time of displaying the notification on the screen in seconds.
    *      'life_time'     => (int)    The lifetime of the notification in seconds.
    *    ]
    * @param array $subscribers
    * @return stdClass|mixed
    */

    public function multicastSend($platformCode, $multicast, $subscribers = array())
    {
        $data = array();
        $data['platform_code'] = $platformCode;
        if (!empty($subscribers)) {
            $data['subscribers'] = $subscribers;
        }
        $data['multicast'] = json_encode($multicast);

        return $this->sendRequest('multicast/send', 'POST', $data);
    }
}
