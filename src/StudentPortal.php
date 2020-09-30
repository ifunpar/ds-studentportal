<?php

namespace Desso\Services;

use Chez14\Desso\ServiceBase;
use Chez14\Desso\Client;

class StudentPortal extends ServiceBase
{
    const
        BASE_URL = "https://studentportal.unpar.ac.id",
        IGNITE_URL = "/C_home/sso_login";

    protected
        $guzzleClient,
        $guzzleSetting = [],
        $guzzleHandlerStack,
        $cookieJar,
        $cookieFile,
        $tempFolder,
        $useTempCookie = true;

    /**
     * Create StudentPortal Instance
     * 
     * Create StudentPortal Instance to access Student Portal Programatically.
     * 
     * Supported parameters are:
     *   - `temp-folder` (string) Location to put teporary files. Default
     *     to `__DIR__ . '/../tmp'`.
     * 
     *   - `cookie` (string) Location to put cookiejar. You might save this for
     *     future access so you don't have to relogin while the token still
     *     active. Default to... Guzzle's default settings.
     *   
     *   - `guzzle` (array) Guzzle settings. Please consult to GuzzleHttp's
     *     documentation for references. 
     * 
     * @see http://docs.guzzlephp.org/en/stable/request-options.html GuzzleHttp
     * Request Options
     *
     * @param array $params Array-based parameters
     */
    public function __construct($params = [])
    {

        /**
         * Temporary Folder
         */
        if (\key_exists('temp-folder', $params)) {
            $this->tempFolder = $params['temp-folder'];
        } else {
            $this->tempFolder = __DIR__ . '/../tmp';
        }
        if (!is_dir($this->tempFolder)) {
            mkdir($this->tempFolder, 0777, true);
        }


        /**
         * Cookie
         */
        $this->guzzleHandlerStack = \GuzzleHttp\HandlerStack::create();
        if (key_exists("cookie", $params)) {
            $this->cookieJarUse($params['cookie'], false);
        } else {
            $this->resetCookie(false);
        }

        $this->guzzleSetting = [
            'base_uri' => self::BASE_URL,
            'allow_redirects' => false,
            'headers' => [
                'User-Agent' => Client::$user_agent
            ],
            'handler' => $this->guzzleHandlerStack,
            'cookies' => $this->cookieJar,
        ];

        /**
         * Guzzle
         */
        if (key_exists("guzzle", $params)) {
            $this->guzzleSetting = array_merge($this->guzzleSetting, $params['guzzle']);
        }
        $this->refreshGuzzle();
    }


    /**
     * Loads cookie Jar/Create them new.
     *
     * @param $cookiejar
     * @param bool $resetGuzzle
     *
     * @return void
     */
    public function cookieJarUse($cookiejar, $resetGuzzle = true): void
    {
        $this->cookieFile = $cookiejar;
        $this->useTempCookie = false;

        $this->cookieJar = new \GuzzleHttp\Cookie\FileCookieJar($cookiejar, true);

        if ($resetGuzzle) {
            $this->refreshGuzzle();
        }
    }

    /**
     * Save cookie Jar to certain place.
     *
     * @param null $saveTo File to save cookie jar to
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function cookieJarSave($saveTo = null): void
    {
        if ($saveTo == null) {
            if ($this->useTempCookie) {
                throw new \InvalidArgumentException("This time, \$saveTo is not allowed to be null.");
            }
            $saveTo = $this->cookieFile;
        }
        $this->cookieJar->save($saveTo);
    }

    /**
     * Gunakan ini untuk membersihkan cookie yang barusan anda load.
     * Method ini akan membuat CookieJar baru. Dan yang lama tidak akan
     * terpengaruh.
     *
     * @param bool $resetGuzzle
     *
     * @return void
     */
    public function resetCookie($resetGuzzle = true): void
    {
        $tmpfname = tempnam($this->tempFolder, "cookie");
        $this->useTempCookie = true;
        $this->cookieFile = $tmpfname;
        $this->cookieJar = new \GuzzleHttp\Cookie\FileCookieJar($tmpfname);

        if ($resetGuzzle) {
            $this->refreshGuzzle();
        }
    }

    /**
     * Auto delete some temps.
     */
    public function __destruct()
    {
        if ($this->useTempCookie)
            unlink($this->cookieFile);
    }

    /**
     * Refresh the Guzzle instance, just in case if you made a changes in cookie
     * or settings.
     *
     * @return void
     */
    protected function refreshGuzzle(): void
    {
        $this->guzzleClient = new \GuzzleHttp\Client($this->guzzleSetting);
    }

    /**
     * Pre Login
     *
     * @return void
     */
    public function pre_login(): void
    {
        $resp = $this->guzzleClient->request('GET', "/");
        $this->guzzleClient->request('GET', self::IGNITE_URL);
        return;
    }

    /**
     * Post Login
     *
     * @param String $ticket
     * @return bool
     */
    public function post_login(String $ticket): bool
    {
        $resp = $this->guzzleClient->request('GET', self::IGNITE_URL, [
            'query' => [
                'ticket' => $ticket
            ],
            'headers' => [
                'Referer' => 'https://sso.unpar.ac.id/login?service=https%3A%2F%2Fstudentportal.unpar.ac.id%2FC_home%2Fsso_login',
            ]
        ]);
        $validation = $resp->getStatusCode() == 302;
        $this->guzzleClient->request('GET', $resp->getHeader('Location')[0]);
        return $validation;
    }

    /**
     * Get the service url
     *
     * @return String
     */
    public function get_service(): String
    {
        return self::BASE_URL . self::IGNITE_URL;
    }

    /**
     * Confirming that login is successfull.
     *
     * @return bool
     */
    public function validateLogin(): bool
    {
        $resp = $this->guzzleClient->request('GET', "/home", []);
        return $resp->getStatusCode() == 200;
    }

    /**
     * APIS ARE PROVIDED HERE
     */

    /**
     * Get Profile APIS
     *
     * @return Data\Profile
     */
    public function getProfile(): Data\Profile
    {
        $profiler = new Data\Profile($this->guzzleClient, []);
        return $profiler;
    }

    /**
     * Get Jadwal APIS
     *
     * @return Data\Jadwal
     */
    public function getJadwal(): Data\Jadwal
    {
        $profiler = new Data\Jadwal($this->guzzleClient, []);
        return $profiler;
    }

    /**
     * Get Nilai APIS
     *
     * @return Data\Nilai
     */
    public function getNilai(): Data\Nilai
    {
        $profiler = new Data\Nilai($this->guzzleClient, [
            "tempFolder" => $this->tempFolder
        ]);
        return $profiler;
    }
}
