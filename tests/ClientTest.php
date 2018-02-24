<?php

namespace Webgriffe\AmpMagentoSoapClient;

use Amp\Loop;
use PHPUnit\Framework\TestCase;
use React\Promise\FulfilledPromise;

class ClientTest extends TestCase
{
    private $soapClient;
    private $client;

    protected function setUp()
    {
        $this->soapClient = $this->prophesize(\Clue\React\Soap\Client::class);
        $this->client = new Client($this->soapClient->reveal(), 'user', 'pass');
    }

    public function testLogin()
    {
        $sessionId = uniqid('', true);
        $this->soapClient->soapCall('login', ['user', 'pass'])->shouldBeCalled()->willReturn(
            new FulfilledPromise($sessionId)
        );

        Loop::run(function () use ($sessionId) {
            $this->assertEquals($sessionId, yield $this->client->login());
        });
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Magento client has not been logged in yet. You must call "login" before "call".
     */
    public function testCallShouldThrowAnErrorWithNotLoggedInClient()
    {
        Loop::run(function () {
            yield $this->client->call('method', []);
        });
    }

    public function testCallShouldReturnSoapCallResult()
    {
        $sessionId = uniqid('', true);
        $this->soapClient->soapCall('login', ['user', 'pass'])->shouldBeCalled()->willReturn(
            new FulfilledPromise($sessionId)
        );
        $soapCallResult = 'result';
        $this->soapClient->soapCall('call', [$sessionId, 'method', []])->shouldBeCalled()->willReturn(
            new FulfilledPromise($soapCallResult)
        );
        Loop::run(function () use ($soapCallResult) {
            yield $this->client->login();
            $this->assertEquals($soapCallResult, yield $this->client->call('method', []));
        });
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Magento client has not been logged in yet. You must call "login" before "endSession".
     */
    public function testEndSessionShouldThrowAnErrorWithNotLoggedInClient()
    {
        Loop::run(function () {
            yield $this->client->endSession();
        });
    }

    public function testEndSessionShouldReturnSoapCallResult()
    {
        $sessionId = uniqid('', true);
        $this->soapClient->soapCall('login', ['user', 'pass'])->shouldBeCalled()->willReturn(
            new FulfilledPromise($sessionId)
        );
        $this->soapClient->soapCall('endSession', [$sessionId])->shouldBeCalled()->willReturn(
            new FulfilledPromise(true)
        );
        Loop::run(function () {
            yield $this->client->login();
            $this->assertTrue(yield $this->client->endSession());
        });
    }
}
