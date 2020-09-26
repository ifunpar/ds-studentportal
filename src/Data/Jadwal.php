<?php

namespace Chez14\Desso\Services\Data;


class Jadwal
{
    protected
        $guzzleClient,
        $config;

    protected
        $data_jadwals = '/<tr>(.*)<\/tr>/sU',
        $data_tr = '/<td.*>(.*)<\/td>/sU',
        $data_dosen = '/<li>(.*)<\/li>/sU';

    protected $datas_jadwal = [
            "day",
            "time",
            "lecture_code",
            "room",
            "lecture_name",
            "sks",
            "class",
            "lecturers",
            "temu"
        ],
        $datas_uts = [
            "number",
            "lecture_code",
            "lecture_name",
            "sks",
            "lecture_class",
            "exam_date",
            "exam_time",
            "room",
            "chair"
        ];

    private
        $fetched_data = null,
        $fetched_html = [];

    /**
     * NOT FOR PUBLIC, PLEASE STAND BACK!
     */
    public function __construct($guzzle, $config)
    {
        $this->guzzleClient = $guzzle;
        $this->config = $config;
    }

    public function getJadwals($refetch = false)
    {
        if (
            !array_key_exists('/jadwal', $this->fetched_html) ||
            $this->fetched_html['/jadwal'] == null ||
            $refetch
        ) {
            $this->fetch('/jadwal');
        }

        $matched_jadwals = [];
        preg_match_all($this->data_jadwals, $this->fetched_html['/jadwal'], $matched_jadwals);

        // var_dump($matched_jadwals);

        $matched_jadwals = array_map(function ($data) {
            $datax = [];
            \preg_match_all($this->data_tr, $data, $datax);

            $kambing = [];
            for ($i = 0; $i < count($this->datas_jadwal); $i++) {
                $kambing[$this->datas_jadwal[$i]] = $datax[1][$i];
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


    public function getUTSes($refetch = false)
    {
        if (
            !array_key_exists('/jadwal/ujian_tengah_semester', $this->fetched_html) ||
            $this->fetched_html['/jadwal/ujian_tengah_semester'] == null ||
            $refetch
        ) {
            $this->fetch('/jadwal/ujian_tengah_semester');
        }

        $tbody = [];
        preg_match_all("/<tbody.*>(.*)<\/tbody>/sU", $this->fetched_html['/jadwal/ujian_tengah_semester'], $tbody);

        $matched_jadwals = [];
        preg_match_all($this->data_jadwals, $tbody[1][0], $matched_jadwals);

        $matched_jadwals = array_map(function ($data) {
            $datax = [];
            \preg_match_all($this->data_tr, $data, $datax);

            $kambing = [];
            for ($i = 0; $i < count($this->datas_uts); $i++) {
                $kambing[$this->datas_uts[$i]] = $datax[1][$i];
            }

            $jam = explode("-", $kambing['exam_time']);
            $kambing['exam_time'] = [
                "start" => $jam[0],
                "end" => $jam[1]
            ];
            return $kambing;
        }, $matched_jadwals[1]);

        return $matched_jadwals;
    }

    public function getUASes($refetch = false)
    {
        if (
            !array_key_exists('/jadwal/ujian_akhir_semester', $this->fetched_html) ||
            $this->fetched_html['/jadwal/ujian_akhir_semester'] == null ||
            $refetch
        ) {
            $this->fetch('/jadwal/ujian_akhir_semester');
        }

        $tbody = [];
        preg_match_all("/<tbody.*>(.*)<\/tbody>/sU", $this->fetched_html['/jadwal/ujian_akhir_semester'], $tbody);

        $matched_jadwals = [];
        preg_match_all($this->data_jadwals, $tbody[1][0], $matched_jadwals);

        $matched_jadwals = array_map(function ($data) {
            $datax = [];
            \preg_match_all($this->data_tr, $data, $datax);

            $kambing = [];
            for ($i = 0; $i < count($this->datas_uts); $i++) {
                $kambing[$this->datas_uts[$i]] = $datax[1][$i];
            }

            $jam = explode("-", $kambing['exam_time']);
            $kambing['exam_time'] = [
                "start" => $jam[0],
                "end" => $jam[1]
            ];
            return $kambing;
        }, $matched_jadwals[1]);

        return $matched_jadwals;
    }



    protected function fetch($endpoint)
    {

        $resp = $this->guzzleClient->request('GET', $endpoint, [], [
            "allow_redirects" => false
        ]);

        $this->fetched_html[$endpoint] = $resp->getBody();
    }
}
