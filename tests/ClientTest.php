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
        $this->assertCount(3, $client->getConfig());
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
        $this->assertEmpty($client->getApiKey());
    }
    
    public function testSetApiKeyConstructor()
    {
        $client = new Client(array('api_key' => 'fdsafdsa'));
        $this->assertEquals($client->getApiKey(), 'fdsafdsa');
    }
    
    public function testSetApiSecretReturnInstance()
    {
        $client = new Client;
        $this->assertInstanceOf('JaegerApp\Rest\Client', $client->setApiSecret('fdsafdsa'));
    }
    
    public function testSetSecretKeyValue()
    {
        $client = new Client;
        $key = $client->setApiSecret('fdsafdsa')->getApiSecret();
        $this->assertEquals($key, 'fdsafdsa');
    }
    
    public function testGetApiSecretDefaultValue()
    {
        $client = new Client;
        $this->assertEmpty($client->getApiSecret());
    }
    
    public function testSetApiSecretConstructor()
    {
        $client = new Client(array('api_secret' => 'fdsafdsa'));
        $this->assertEquals($client->getApiSecret(), 'fdsafdsa');
    }

    public function testSetSiteUrlReturnInstance()
    {
        $client = new Client;
        $this->assertInstanceOf('JaegerApp\Rest\Client', $client->setSiteUrl('fdsafdsa'));
    }
    
    public function testSetSiteUrlValue()
    {
        $client = new Client;
        $key = $client->setSiteUrl('fdsafdsa')->getSiteUrl();
        $this->assertEquals($key, 'fdsafdsa');
    }
    
    public function testGetSiteUrlDefaultValue()
    {
        $client = new Client;
        $this->assertEmpty($client->getSiteUrl());
    }
    
    public function testSetSiteUrlConstructor()
    {
        $client = new Client(array('site_url' => 'fdsafdsa'));
        $this->assertEquals($client->getSiteUrl(), 'fdsafdsa');
    }    
}