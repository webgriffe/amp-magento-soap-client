<?php

namespace Webgriffe\AmpMagentoSoapClient;

use Amp\CallableMaker;
use Amp\Promise;
use function Amp\Promise\adapt;
use Amp\ReactAdapter\ReactAdapter;
use Clue\React\Buzz\Browser;
use Clue\React\Soap\Client as SoapClient;
use Clue\React\Soap\Factory as SoapClientFactory;
use React\Socket\Connector;

class Factory
{
    use CallableMaker;

    /**
     * @var string
     */
    private $wsdl;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $nameserver;

    public function __construct(string $wsdl, string $username, string $password, string $nameserver = null)
    {
        $this->wsdl = $wsdl;
        $this->username = $username;
        $this->password = $password;
        $this->nameserver = $nameserver;
    }

    /**
     * @return Promise
     * @throws \Error
     */
    public function create(): Promise
    {
        $loop = ReactAdapter::get();
        $nameserver = [];
        if (null !== $this->nameserver) {
            $nameserver = ['dns' => $this->nameserver];
        }
        $connector = new Connector($loop, $nameserver);
        $browser = new Browser($loop, $connector);
        $factory = new SoapClientFactory($loop, $browser);

        return adapt(
            $factory->createClient($this->wsdl)->then(
                function (SoapClient $soapClient): Client {
                    return new Client($soapClient, $this->username, $this->password);
                }
            )
        );
    }
}
