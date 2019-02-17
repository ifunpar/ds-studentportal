<?php
use PHPUnit\Framework\TestCase;
use Chez14\Desso;

/**
 * @testdox Basic Test
 */
class BasicTest extends TestCase
{
    protected $client;
    protected $stupor;
    
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

        $this->client = $client;
    }

    /**
     * @testdox Able to perform login.
     */
    public function testFirstStage() {
        $stupor = new Services\StudentPortal();
        $client = $this->client;

        $this->assertTrue($client->serviceLogin($stupor));
    }
}