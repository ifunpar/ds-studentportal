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
    public function testGetProfiles() {
        $stupor = $this->stupor;
        $jadwal = $stupor->getProfile();
        $this->assertNotNull($jadwal->getDatas());
        $this->assertNotNull($jadwal->getProfilePict_base64());
    }

    /**
     * @testdox Fetch and Parse Jadwal
     */
    public function testGetJadwals() {
        $stupor = $this->stupor;
        $jadwal = $stupor->getJadwal();
        $this->assertNotNull($jadwal->getJadwals());
        if($jadwal->getUASes()) {
            $this->assertNotNull($jadwal->getUTSes());
        }
    }

    /**
     * @testdox Fetch and Parse Nilai
     */
    public function testGetNilais() {
        $stupor = $this->stupor;
        $jadwal = $stupor->getNilai();
        $this->assertNotNull($jadwal->getNilais());
        
        $semesters = null;
        $this->assertNotNull($semesters = $jadwal->getSemesters());
        
        $this->assertNotNull($jadwal->getNilais($semesters[0]));
    }
}