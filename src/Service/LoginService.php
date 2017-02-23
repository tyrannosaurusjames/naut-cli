<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\DomCrawler\Crawler;

class LoginService
{

    public function login(Client $client)
    {
        $response = $client->get('/Security/login');
        $crawler = new Crawler($response->getBody()->getContents());

        try {
            $securityId = $crawler->filter('#MemberLoginForm_LoginForm_SecurityID')->first()->attr('value');
        } catch (\InvalidArgumentException $e) {
            throw new BadResponseException('Couldn\'t find login form security id', new Request('', ''));
        }

        $response = $client->request('POST', '/Security/LoginForm', [
            'allow_redirects' => false,
            'form_params' => [
                'SecurityID' => $securityId,
                'AuthenticationMethod' => 'MemberAuthenticator',
                'Email' => getenv('NAUT_USERNAME'),
                'Password' => base64_decode(getenv('NAUT_PASSWORD_B64'))
            ]
        ]);

        $location = $response->getHeader('Location');

        if ($location[0] !== getenv('NAUT_URL') . '/') {
            return false;
        } else {
            return true;
        }
    }

}
