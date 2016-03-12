<?php
/**
 * Backup Pro - REST Client
 *
 * @copyright	Copyright (c) 2016, mithra62, Eric Lamb.
 * @link		http://backup-pro.com/
 * @version		1.0
 * @filesource 	./mithra62/BpApiClient/Client.php
 */
 
namespace mithra62\BpApiClient;

use PhilipBrown\Signature\Token;
use PhilipBrown\Signature\Request;

/**
 * Rest Client Object
 *
 * Simple object to interact with a Backup Pro installation
 * 
 * Shout out to the LinkedIn REST Api client for me stealing
 * their design ;)
 * 
 * @see https://github.com/ashwinks/PHP-LinkedIn-SDK
 *
 * @package BackupPro\View
 * @author Eric Lamb <eric@mithra62.com>
 */
class Client
{
    /**
     * The configuration details for connection
     * @var array
     */
    protected $config = array();
    
    /**
     * The API Key to use
     * @var string
     */
    protected $api_key = null;
    
    /**
     * The API Secret to use
     * @var string
     */
    protected $api_secret = null;
    
    /**
     * The debug information
     * @var array
     */
    protected $debug_info = array();
    
    /**
     * The Curl handle
     * @var resource
     */
    protected $curl = null;
    
    /**
     * The HTTP verb names
     * @var string
     */
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';
    
    /**
     * Sets it up
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Sets the API key to use for authentication
     * @param string $key
     * @return \mithra62\BpApiClient\Client
     */
    public function setApiKey($key)
    {
        $this->api_key = $key;
        return $this;
    }
    
    /**
     * Returns the API key 
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }
    
    /**
     * Sets the API secret to use for authentication
     * @param string $secret
     * @return \mithra62\BpApiClient\Client
     */
    public function setApiSecret($secret)
    {
        $this->api_secret = $secret;
        return $this;
    }
    
    /**
     * Returns the API secret
     * @return string
     */
    public function getApiSecret()
    {
        return $this->api_secret;
    }
    
    /**
     * Send a POST request
     * @param string $endpoint The API endpoint
     * @param array $payload Data to submit
     */
    public function post($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload, self::HTTP_METHOD_POST);
    }
    
    /**
     * Sends a GET request
     * @param string $endpoint The API endpoint
     * @param array $payload
     */
    public function get($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload);
    }
    /**
     * PUT to an authenciated API endpoint w/ payload
     *
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    public function put($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload, self::HTTP_METHOD_PUT);
    }
    
    public function delete($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload, self::HTTP_METHOD_DELETE);
    }
    
    /**
     * Make an authenticated API request to the specified endpoint
     * Headers are for additional headers to be sent along with the request.
     * Curl options are additional curl options that may need to be set
     *
     * @param string $endpoint
     * @param array $payload
     * @param string $method
     * @return array
     */
    public function fetch($endpoint, array $payload = array(), $method = 'GET')
    {
        $token   = new Token($this->getApiKey(), $this->getApiSecret());
        $request = new Request($method, $endpoint, $payload);
        $headers = $request->sign($token);
        
        return $this->_makeRequest($endpoint, $payload, $method, $headers);
    }    
    
    /**
     * Get debug info from the CURL request
     *
     * @return array
     */
    public function getDebugInfo()
    {
        return $this->debug_info;
    }
    
    /**
     * Make a CURL request
     *
     * @param string $url
     * @param array $payload
     * @param string $method
     * @param array $headers
     * @param array $curl_options
     * @throws \RuntimeException
     * @return array
     */
    protected function _makeRequest($url, array $payload = array(), $method = 'GET', array $headers = array(), array $curl_options = array())
    {
        $ch = $this->getCurlHandle();
        $options = array(
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        );
        if (!empty($payload)) {
            if ($options[CURLOPT_CUSTOMREQUEST] == self::HTTP_METHOD_POST || $options[CURLOPT_CUSTOMREQUEST] == self::HTTP_METHOD_PUT) {
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = json_encode($payload);
                $headers[] = 'Content-Length: ' . strlen($options[CURLOPT_POSTFIELDS]);
                $headers[] = 'Content-Type: application/json';
                $options[CURLOPT_HTTPHEADER] = $headers;
            } else {
                $options[CURLOPT_URL] .= '&' . http_build_query($payload, '&');
            }
        }
        if (!empty($curl_options)) {
            $options = array_replace($options, $curl_options);
        }
        if (isset($this->config['curl_options']) && !empty($this->config['curl_options'])) {
            $options = array_replace($options, $this->config['curl_options']);
        }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $this->_debug_info = curl_getinfo($ch);
        if ($response === false) {
            throw new \RuntimeException('Request Error: ' . curl_error($ch));
        }
        $response = json_decode($response, true);
        if (isset($response['status']) && ($response['status'] < 200 || $response['status'] > 300)) {
            throw new \RuntimeException('Request Error: ' . $response['message'] . '. Raw Response: ' . print_r($response, true));
        }
        return $response;
    }
    
    
    protected function getCurlHandle()
    {
        if (!$this->curl_handle) {
            $this->curl_handle = curl_init();
        }
        return $this->curl_handle;
    }
    
    public function __destruct()
    {
        if ($this->curl_handle) {
            curl_close($this->curl_handle);
        }
    }
}