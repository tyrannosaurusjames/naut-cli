<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class LoginService
{

    public function login(Client $client)
    {
        $response = $client->get('/Security/login');
        $body = $response->getBody()->getContents();

        $crawler = new Crawler($body);

        $securityId = $crawler->filter('#MemberLoginForm_LoginForm_SecurityID')->first()->attr('value');

        $response = $client->request('POST', '/Security/LoginForm', [
            'form_params' => [
                'SecurityID' => $securityId,
                'AuthenticationMethod' => 'MemberAuthenticator',
                'Email' => getenv('NAUT_USERNAME'),
                'Password' => base64_decode(getenv('NAUT_PASSWORD_B64'))
            ]
        ]);
    }

}
