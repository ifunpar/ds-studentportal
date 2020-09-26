<?php

use PHPUnit\Framework\TestCase;
use Chez14\Desso;
use Desso\Services;

/**
 * @testdox Basic Test
 */
class BasicTest extends TestCase
{
    protected $client;
    protected $stupor;
    protected $cacheFolder;

    protected function setUp(): void
    {
        $client = new Desso\Client();
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();

        $client->setCredential($_ENV['DS_UNAME'], $_ENV['DS_PASSWD']);

        if (!$client->login()) {
            throw new \InvalidArgumentException('Wrong credential!');
        }

        if (!is_dir(__DIR__ . "/../cache/")) {
            mkdir(__DIR__ . "/../cache/");
        }

        $this->cacheFolder = realpath(__DIR__ . "/../cache/");

        $this->client = $client;

        if (is_file($this->cacheFolder . "/sesi.cookie")) {
            unlink($this->cacheFolder . "/sesi.cookie");
        }
    }

    /**
     * @testdox Able to perform login.
     */
    public function testFirstStage()
    {
        $stupor = new Services\StudentPortal([
            'cookie' => $this->cacheFolder . "/sesi.cookie"
        ]);
        $client = $this->client;

        $this->assertTrue($client->serviceLogin($stupor));
        $this->assertTrue($stupor->validateLogin());
    }
}
