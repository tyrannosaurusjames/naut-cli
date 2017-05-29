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

        $doDeployActionCount = $crawler->filter('input[name="action_doDeploy"]')->count();

        $formParams = [
            'SecurityID' => $securityId,
            'Branch' => $branchIdentifier,
            'SelectRelease' => 'Branch'
        ];

        if ($doDeployActionCount === 0) {
            // pipeline deploy
            $formParams['SkipSnapshot'] = '1';
            $formParams['action_startPipeline'] = 'Go!';
            $followRedirects = true;
        } else {
            // standard deploy
            $formParams['action_doDeploy'] = 'Go!';
            $followRedirects = false;
        }

        $response = $client->request('POST', '/naut/project/' . $instance . '/environment/' . $environment . '/DeployForm', [
            'form_params' => $formParams,
            'allow_redirects' => $followRedirects
        ]);

        if ($doDeployActionCount === 0) {
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
        } else {
            $location = $response->getHeader('Location')[0];

            $logLink = getenv('NAUT_URL') . $location . '/log';
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
