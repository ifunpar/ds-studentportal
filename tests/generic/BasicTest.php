<?php
use PHPUnit\Framework\TestCase;
use Chez14\Desso;
use Chez14\Desso\Services;

use GuzzleHttp\HandlerStack;
use Namshi\Cuzzle\Middleware\CurlFormatterMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * @testdox Basic Test
 */
class BasicTest extends TestCase
{
    protected $client;
    protected $stupor;
    protected $cacheFolder;
    
    protected function setUp() {
        $client = new Desso\Client();
        if(is_file(__DIR__ . "/../.env")) {
            $credential = json_decode(file_get_contents(__DIR__ . "/../.env"), true);
            $client->setCredential($credential['username'], $credential['password']);
        } else {
            $client->setCredential(getenv('DS_UNAME'), getenv('DS_PASSWD'));
        }

        if(!$client->login()){
            throw new \InvalidArgumentException('Wrong credential!');
        }

        if(!is_dir(__DIR__ . "/../cache/")){
            mkdir(__DIR__ . "/../cache/");
        }

        $this->cacheFolder = realpath(__DIR__ . "/../cache/");

        $this->client = $client;
    }

    /**
     * @testdox Able to perform login.
     */
    public function testFirstStage() {
        $stupor = new Services\StudentPortal([
            'cookie'=>$this->cacheFolder . "/sesi.cookie"
        ]);
        $client = $this->client;

        $this->assertTrue($client->serviceLogin($stupor));
    }
}