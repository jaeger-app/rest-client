<?php
/**
 * Jaeger - REST Client
 *
 * @copyright	Copyright (c) 2016, mithra62, Eric Lamb.
 * @link		http://jaegerapp.net/
 * @version		1.0
 * @filesource 	./jaeger-app/Rest/Client.php
 */
 
namespace JaegerApp\Rest;

use PhilipBrown\Signature\Token;
use PhilipBrown\Signature\Request;

/**
 * Rest Client Object
 *
 * Simple object to interact with a Jaeger installation
 *
 * @package Rest\Client
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
     * The URL to the Backup Pro API endpoint
     * @var string
     */
    protected $site_url = null;
    
    /**
     * The debug information
     * @var string
     */
    protected $debug_info = array();
    
    /**
     * The Curl handle
     * @var resource
     */
    protected $curl_handle = null;
    
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
    public function __construct(array $config = array())
    {
        if(isset($config['api_key'])) {
            $this->api_key = $config['api_key'];
        }
        
        if(isset($config['api_secret'])) {
            $this->api_secret = $config['api_secret'];
        }
        
        if(isset($config['site_url'])) {
            $this->site_url = $config['site_url'];
        }
        
        $this->config = $config;
    }
    
    /**
     * Sets the API key to use for authentication
     * @param string $key
     * @return \JargerApp\Rest\Client
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
     * @return \JargerApp\Rest\Client
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
     * Sets the Backup Pro REST API site URL 
     * @param string $site_url
     * @return \JargerApp\Rest\Client
     */
    public function setSiteUrl($site_url)
    {
        $this->site_url = $site_url;
        return $this;
    }
    
    /**
     * Returns the Backup Pro REST API site URL
     * @param string $endpoint
     * @return string
     */
    public function getSiteUrl($endpoint = '', array $query = array())
    {
        if(count($query) != '0') {
            $endpoint .= '&'.http_build_query($query);
        }
        
        return $this->site_url.$endpoint;
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
    
    /**
     * Performs a DELETE request
     * @param string $endpoint
     * @param array $payload
     * @return bool
     */
    public function delete($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload, self::HTTP_METHOD_DELETE);
    }
    
    /**
     * Sets up the Hmac authentication headers and dispatches the request
     * @param string $endpoint The API endpoing we want
     * @param array $payload Any data to send along
     * @param string $method The HTTP method 
     * @return bool
     */
    public function fetch($endpoint, array $payload = array(), $method = 'GET')
    {
        //so we don't want to use the query params for HMAC since we won't know what's 
        //being sent versus what's expected to be sent. So we remove them and prepare for 
        //query usage
        $query = array();
        if(strtolower($method)== 'get') {
            $query = $payload;
            $payload = array();
        }
        
        $token   = new Token($this->getApiKey(), $this->getApiSecret());
        $request = new Request($method, $endpoint, $payload);
        $headers = $request->sign($token, 'm62_auth_');
        
        if(strtolower($method)== 'get') {
            $payload = array();
        }
        
        $endpoint = $this->getSiteUrl($endpoint, $query);
        return $this->request($endpoint, $payload, $method, $headers);
    }    
    
    /**
     * Returns the debug data
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
    protected function request($url, array $payload = array(), $method = 'GET', array $headers = array(), array $curl_options = array())
    {
        $ch = $this->getCurlHandle();
        $parsed_headers = array();
        foreach($headers AS $key => $value) {
            $parsed_headers[] = $key.': '.$value;
        }
        
        $options = array(
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $parsed_headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        );
        if (!empty($payload)) {
            if ($options[CURLOPT_CUSTOMREQUEST] == self::HTTP_METHOD_POST || 
                $options[CURLOPT_CUSTOMREQUEST] == self::HTTP_METHOD_PUT || 
                $options[CURLOPT_CUSTOMREQUEST] == self::HTTP_METHOD_DELETE) {
                $json_payload = json_encode($payload);
                
                $options[CURLOPT_POSTFIELDS] = $json_payload;
                $parsed_headers[] = 'Content-Length: ' . strlen($json_payload);
                $parsed_headers[] = 'Content-Type: application/json';
                $options[CURLOPT_HTTPHEADER] = $parsed_headers;
            } 
        }
        if (!empty($curl_options)) {
            $options = array_replace($options, $curl_options);
        }
        if (isset($this->config['curl_options']) && !empty($this->config['curl_options'])) {
            $options = array_replace($options, $this->config['curl_options']);
        }
        
        curl_setopt_array($ch, $options);
        $response_raw = curl_exec($ch);
        $this->debug_info = curl_getinfo($ch);

        if ($response_raw === false) {
            throw new \RuntimeException('Request Error: ' . curl_error($ch));
        }
        
        curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($response_raw, true);
        if (isset($response['status']) && ($response['status'] < 200 || $response['status'] > 300)) {
            return Client\ApiProblem::fromJson($response_raw); 
        }
        
        return Client\Hal::fromJson($response_raw, 3);
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