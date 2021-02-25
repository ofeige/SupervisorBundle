<?php declare(strict_types=1);

namespace YZ\SupervisorBundle\HttpClient;

use fXmlRpc\Client;
use fXmlRpc\ClientInterface;
use fXmlRpc\Transport\HttpAdapterTransport;
use fXmlRpc\Transport\TransportInterface;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\Authentication\BasicAuth;

class HttpClientFactory
{
    public function createClient(string $ipAddress, int $port, string $username = '', string $password = ''): ClientInterface
    {
        if ($username !== '') {
            $httpTransport = $this->createTransportWithBasicAuth($username, $password);
        } else {
            $httpTransport = $this->createTransport(HttpClientDiscovery::find());
        }

        return new Client('http://' . $ipAddress . ':' . $port . '/RPC2/', $httpTransport);
    }

    private function createTransportWithBasicAuth(string $username, string $password): TransportInterface
    {
        $authentication = new BasicAuth($username, $password);
        $authenticationPlugin = new AuthenticationPlugin($authentication);

        $pluginClient = new PluginClient(HttpClientDiscovery::find(), [$authenticationPlugin]);

        return $this->createTransport($pluginClient);
    }

    private function createTransport(HttpClient $httpClient): TransportInterface
    {
        return new HttpAdapterTransport(MessageFactoryDiscovery::find(), $httpClient);
    }
}