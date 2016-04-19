<?php
/**
 * Jaeger
 *
 * @author		Eric Lamb <eric@mithra62.com>
 * @copyright	Copyright (c) 2015-2016, mithra62, Eric Lamb
 * @link		http://jaeger-app.com
 * @version		1.0
 * @filesource 	./tests/ClientTest.php
 */
namespace JaegerApp\tests;

use JaegerApp\Rest\Client;

/**
 * Jaeger - REST Client object Unit Tests
 *
 * Contains all the unit tests for the \JaegerApp\Rest\Client object
 *
 * @package Jaeger\Tests
 * @author Eric Lamb <eric@mithra62.com>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfigValue()
    {
        $client = new Client;
        $this->assertTrue(is_array($client->getConfig()));
        $this->assertCount(0, $client->getConfig());
    }
    
    public function testSetApiKeyReturnInstance()
    {
        $client = new Client;
        $this->assertInstanceOf('JaegerApp\Rest\Client', $client->setApiKey('fdsafdsa'));
    }
    
    public function testSetApiKeyValue()
    {
        $client = new Client;
        $key = $client->setApiKey('fdsafdsa')->getApiKey();
        $this->assertEquals($key, 'fdsafdsa');
    }
    
    public function testGetApiKeyDefaultValue()
    {
        $client = new Client;
        $this->assertNull($client->getApiKey());
    }
    
    public function testSetApiKeyConstructor()
    {
        $client = new Client(array('api_key' => 'fdsafdsa'));
        $this->assertEquals($client->getApiKey(), 'fdsafdsa');
        $this->assertCount(1, $client->getConfig());
    }
}