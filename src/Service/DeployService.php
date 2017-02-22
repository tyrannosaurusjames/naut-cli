<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class DeployService
{

    public function deploy(Client $client, $instance, $environment, $branch)
    {
        $response = $client->get('/naut/project/' . $instance . '/environment/' . $environment);

        $crawler = new Crawler($response->getBody()->getContents());
        $branchIdentifier = $crawler->filter('option[value$="' . $branch . '"]')->first()->attr('value');
        $securityId = $crawler->filter('input[name="SecurityID"]')->first()->attr('value');

        $response = $client->request('POST', '/naut/project/' . $instance . '/environment/' . $environment . '/DeployForm', [
            'form_params' => [
                'SecurityID' => $securityId,
                'Branch' => $branchIdentifier,
                'SelectRelease' => 'Branch',
                'SkipSnapshot' => '1',
                'action_startPipeline' => 'Go!'
            ]
        ]);

        $crawler = new Crawler($response->getBody()->getContents());

        $actions = $crawler->filter('#deployprogress-actions a');

        $deploymentLogLinkFound = false;
        $logLink = '';

        /** @var \DOMElement $action */
        foreach ($actions as $action) {
            $actionTitle = trim($action->textContent);

            if ($actionTitle === 'Deployment Log') {
                $logLink = getenv('NAUT_URL') . '/' . $action->getAttribute('href') . '/log';
                $deploymentLogLinkFound = true;
                break;
            }
        }

        if ($deploymentLogLinkFound === false) {
            $logLink = $this->findDeploymentLogLink($client, $instance, $environment);
        }

        return $logLink;
    }

    private function findDeploymentLogLink(Client $client, $instance, $environment)
    {
        $deploymentLogLinkFound = false;

        $logLink = '';
        $count = 0;

        while ($deploymentLogLinkFound === false) {
            if ($count >= 10) {
                throw new \Exception('Couldn\'t find deployment log link');
            }

            $response = $client->get('/naut/project/' . $instance . '/environment/' . $environment);

            $crawler = new Crawler($response->getBody()->getContents());

            $actions = $crawler->filter('#deployprogress-actions a');

            $deploymentLogLinkFound = false;

            /** @var \DOMElement $action */
            foreach ($actions as $action) {
                $actionTitle = trim($action->textContent);

                if ($actionTitle === 'Deployment Log') {
                    $logLink = getenv('NAUT_URL') . '/' . $action->getAttribute('href') . '/log';
                    $deploymentLogLinkFound = true;
                    break;
                }
            }

            if ($deploymentLogLinkFound === false) {
                $count += 1;
                sleep(2);
            }
        }

        return $logLink;
    }

}
