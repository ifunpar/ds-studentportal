<?php
namespace Chez14\Desso\Services;
use Chez14\Desso\ServiceBase;
use Chez14\Desso\Client;

class StudentPortal extends ServiceBase {
    const
        BASE_URL="https://studentportal.unpar.ac.id/",
        IGNITE_URL="/C_home/sso_login";
    
    protected
        $guzzleClient,
        $guzzleSetting=[],
        $guzzleHandlerStack,
        $cookieJar,
        $cookieFile,
        $tempFolder,
        $useTempCookie = true;

    public function __construct($params = []) {

        /**
         * Temporary Folder
         */
        if(\key_exists('temp-folder', $params)){
            $this->tempFolder = $params['temp-folder'];
        } else {
            $this->tempFolder = __DIR__ . '/../tmp';
        }
        if(!is_dir($this->tempFolder)){
            mkdir($this->tempFolder, 0777,true);
        }


        /**
         * Cookie
         */
        $this->guzzleHandlerStack = \GuzzleHttp\HandlerStack::create();
        if(key_exists("cookie", $params)) {
            $this->cookieJarUse($params['cookie'], false);
        } else {
            $this->resetCookie(false);
        }

        $this->guzzleSetting = [
            'base_uri' => self::BASE_URL,
            'allow_redirects' => [
                'max'             => 5,
                'strict'          => false,
                'referer'         => true,
                'protocols'       => ['https'],
                'track_redirects' => false
            ],
            'headers' => [
                'User-Agent' => Client::$user_agent
            ],
            'handler' => $this->guzzleHandlerStack,
            'cookies' => $this->cookieJar,
        ];

        /**
         * Guzzle
         */
        if(key_exists("guzzle", $params)) {
            $this->guzzleSetting = array_merge($this->guzzleSetting, $params);
        }
        $this->refreshGuzzle();
    }


    /**
     * Loads cookie Jar/Create them new.
     */
    public function cookieJarUse($cookiejar, $resetGuzzle=true) {
        $this->cookieFile = $cookieJar;
        $this->useTempCookie = false;

        $this->cookieJar = new \GuzzleHttp\Cookie\FileCookieJar($cookiejar, true);
        
        if($resetGuzzle) {
            $this->refreshGuzzle();
        }
    }

    /**
     * Save cookie Jar to certain place.
     */
    public function cookieJarSave($saveTo = null) {
        if($saveTo == null){
            if($this->useTempCookie) {
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
     * @param $hardReset Set true untuk menyimpan cookie yang lama.
     */
    public function resetCookie($resetGuzzle = true){
        $tmpfname = tempnam($this->tempFolder, "cookie");
        $this->useTempCookie = true;
        $this->cookieFile = $tmpfname;
        $this->cookieJar = new \GuzzleHttp\Cookie\FileCookieJar($tmpfname, true);

        if($resetGuzzle) {
            $this->refreshGuzzle();
        }
    }

    /**
     * Auto delete some temps.
     */
    public function __destruct() {
        if($this->useTempCookie)
            unlink($this->cookieFile);
    }

    /**
     * Refresh the Guzzle instance, just in case if you made a changes in cookie
     * or settings.
     */
    protected function refreshGuzzle() {
        $this->guzzleClient = new \GuzzleHttp\Client($this->guzzleSetting);
    }



    public function pre_login(){
        $this->client->request('GET', "/");
        $this->client->request('GET', self::IGNITE_URL);

        return;
    }

    public function post_login($ticket) {
        $resp = $this->client->request('GET',self::IGNITE_URL, [
            'query'=>[
                'ticket'=>$ticket
            ]
        ]);
    }

    public function get_service():String {
        return self::BASE_URL . self::IGNITE_URL;
    }

    public function validateLogin() {
        $this->client->request('GET', "/home", [], ["allow_redirects"=>false]);
        return !($resp->getHeader("Location")[0]==self::BASE_URL);
    }

    /**
     * APIS ARE PROVIDED HERE
     */
}