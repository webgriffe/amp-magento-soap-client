<?php

namespace Webgriffe\AmpMagentoSoapClient;

use Amp\CallableMaker;
use Amp\Promise;
use function Amp\Promise\adapt;
use Clue\React\Soap\Client as SoapClient;

class Client
{
    use CallableMaker;

    /**
     * @var Client
     */
    private $soapClient;
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
    private $sessionId;

    public function __construct(SoapClient $soapClient, string $username, string $password)
    {
        $this->soapClient = $soapClient;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return Promise A promise which resolves with the session ID on success.
     * @throws \Error
     */
    public function login(): Promise
    {
        return adapt(
            $this->soapClient->soapCall('login', [$this->username, $this->password])->then(
                function ($sessionId): string {
                    $this->sessionId = (string)$sessionId;
                    return $this->sessionId;
                }
            )
        );
    }

    /**
     * @param string $method
     * @param array $args
     * @return Promise
     * @throws \LogicException
     * @throws \Error
     */
    public function call(string $method, array $args): Promise
    {
        if (!$this->sessionId) {
            throw new \LogicException(
                'Magento client has not been logged in yet. You must call "login" before "call".'
            );
        }
        return adapt($this->soapClient->soapCall('call', [$this->sessionId, $method, $args]));
    }

    /**
     * @return Promise
     * @throws \LogicException
     * @throws \Error
     */
    public function endSession(): Promise
    {
        if (!$this->sessionId) {
            throw new \LogicException(
                'Magento client has not been logged in yet. You must call "login" before "endSession".'
            );
        }
        return adapt($this->soapClient->soapCall('endSession', [$this->sessionId])->then(
            function ($result): bool {
                $this->sessionId = null;
                return (bool)$result;
            }
        ));
    }
}
