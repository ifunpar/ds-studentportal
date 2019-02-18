<?php
namespace Chez14\Desso\Services\Data;
use Chez14\Desso\ServiceBase;
use Chez14\Desso\Client;


class Jadwal {
    protected
        $guzzleClient,
        $config;
    
    protected
        $data_jadwals='/<tr>(.*)<\/tr>/sU',
        $data_tr='/<td.*>(.*)<\/td>/sU',
        $data_dosen='/<li>(.*)<\/li>/sU';

    protected $datas_tr = [
        "day",
        "time",
        "lecture_code",
        "room",
        "lecture_name",
        "sks",
        "class",
        "lecturers",
        "temu"
    ];

    private
        $fetched_data,
        $fetched_html;
    
     /**
      * NOT FOR PUBLIC, PLEASE STAND BACK!
      */
    public function __construct($guzzle, $config) {
        $this->guzzleClient = $guzzle;
        $this->config = $config;
    }

    public function getJadwals($refetch = false) {
        if($this->fetched_data && !$refetch) {
            return $fetched_data;
        }

        if($this->fetched_html['/jadwal'] == null || $refetch) {
            $this->fetch('/jadwal');
        }

        $matched_jadwals = [];
        preg_match_all($this->data_jadwals, $this->fetched_html['/jadwal'], $matched_jadwals);
        
        // var_dump($matched_jadwals);

        $matched_jadwals = array_map(function($data) {
            $datax = [];
            \preg_match_all($this->data_tr, $data, $datax);
            
            $kambing = [];
            for($i=0; $i<count($this->datas_tr); $i++){
                $kambing[$this->datas_tr[$i]] = $datax[1][$i];
            }
            
            $lecturers = [];
            \preg_match_all($this->data_dosen, $kambing['lecturers'], $lecturers);
            $kambing['lecturers'] = $lecturers[1];

            $jam = explode("-", $kambing['time']);
            $kambing['time'] = [
                "start" => $jam[0],
                "end" => $jam[1]
            ];
            return $kambing;
        }, $matched_jadwals[1]);

        return $matched_jadwals;
    }

    

    protected function fetch($endpoint) {
        
        $resp = $this->guzzleClient->request('GET', $endpoint, [], [
            "allow_redirects"=>false
        ]);

        $this->fetched_html[$endpoint] = $resp->getBody();
    }

}