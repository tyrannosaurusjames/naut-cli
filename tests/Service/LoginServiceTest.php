<?php
namespace Guttmann\NautCli\Service;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class LoginServiceTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        putenv('NAUT_URL=https://naut.test.com');
        putenv('NAUT_USERNAME=test@test.com');
        putenv('NAUT_PASSWORD_B64=testpassword');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testLogin()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')->andReturn(new Response(200, [], '<html><body><input id="MemberLoginForm_LoginForm_SecurityID" name="SecurityID" value="test-security-id"></body></html>'));
        $mockClient->shouldReceive('request')->andReturn(new Response(302, ['Location' => getenv('NAUT_URL') . '/'], ''));

        $loginService = new LoginService();
        $loggedIn = $loginService->login($mockClient);

        $this->assertTrue($loggedIn);
    }

    public function testIncorrectLogin()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')->andReturn(new Response(200, [], '<html><body><input id="MemberLoginForm_LoginForm_SecurityID" name="SecurityID" value="test-security-id"></body></html>'));
        $mockClient->shouldReceive('request')->andReturn(new Response(302, ['Location' => getenv('NAUT_URL') . '/Security/login'], ''));

        $loginService = new LoginService();
        $loggedIn = $loginService->login($mockClient);

        $this->assertFalse($loggedIn);
    }

    public function testFailedLogin()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')->andThrow(new RequestException('Test exception', new Request('', '')));

        $thrown = false;

        try {
            $loginService = new LoginService();
            $loginService->login($mockClient);
        } catch (RequestException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }

    public function testMissingSecurityID()
    {
        $mockClient = \Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')->andReturn(new Response(200, [], '<html><body></body></html>'));

        $thrown = false;

        try {
            $loginService = new LoginService();
            $loginService->login($mockClient);
        } catch (RequestException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }

}
