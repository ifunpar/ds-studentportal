<?php
use PHPUnit\Framework\TestCase;
use Chez14\Desso;
use Chez14\Desso\Services;

use GuzzleHttp\HandlerStack;
use Namshi\Cuzzle\Middleware\CurlFormatterMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * @testdox Fetching Profile
 */
class FetchProfileTest extends TestCase
{
    protected $stupor;
    protected $cacheFolder;
    protected $testHandler;
    
    protected function setUp() {
        $this->cacheFolder = realpath(__DIR__ . "/../cache/");
        $this->stupor = new Services\StudentPortal([
            'cookie' => $this->cacheFolder . "/sesi.cookie",
        ]);

        $this->assertTrue($this->stupor->validateLogin());
    }

    /**
     * @testdox Fetch and Parse Profile
     */
    public function testFirstStage() {
        $stupor = $this->stupor;
        $stupor->getProfile();
    }
}