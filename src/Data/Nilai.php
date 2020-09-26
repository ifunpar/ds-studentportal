<?php

namespace Desso\Services\Data;

class Nilai
{
    protected
        $guzzleClient,
        $config;

    protected
        $data_nilai = '/data_mata_kuliah = \[\];(.*)var data_angket = \[\];/sU',
        $data_semesters = '/<select.*id="dropdownSemester">(.*)<\/select>/sU';

    private
        $fetched_data = [],
        $fetched_html = [];

    /**
     * Create Nilai API Endpoint Consumer.
     * 
     * You're not supposed to be initialize this class directly,
     * you have to use
     * {@see \Desso\Services\StudentPortal::getNilai() getNilai} method to be
     * to use this class.
     * 
     * @see \Desso\Services\StudentPortal::getNilai()
     */
    public function __construct(\GuzzleHttp\Client $guzzle, array $config)
    {
        $this->guzzleClient = $guzzle;
        $this->config = $config;
    }

    public function getNilais(Enums\Semester $semester = null, $refetch = false)
    {
        $endpoint = "/nilai";
        if ($semester != null) {
            $endpoint .= "/" . $semester->getUrl();
        }
        $this->autorefetch($endpoint, $refetch);
        $datas = [];

        $matcher = [];
        preg_match_all($this->data_nilai, $this->fetched_html[$endpoint], $matcher);
        $matcher = $matcher[1][0];

        //SANITIZER
        $matcher = \preg_replace(['/var/', '/\;/', '/data_/', '/[a-zA-Z_]+\((.*)\)/sU'], ['', ";\n", '\$data_', ''], $matcher);

        $data_mata_kuliah = [];

        $tmpfs = \tempnam($this->config['tempFolder'], 'phpobj');
        file_put_contents($tmpfs, "<?php\n" . $matcher);
        try {
            include($tmpfs);
        } catch (\Error $e) {
            throw new \UnexpectedValueException("There's something that we cannot eval", 0, $e);
        }

        unlink($tmpfs);

        return $data_mata_kuliah;
    }

    /**
     * Get all avaialble semesters
     */
    public function getSemesters($refetch = false)
    {
        $this->autorefetch('/nilai', $refetch);

        $semesters = [];
        preg_match_all($this->data_semesters, $this->fetched_html['/nilai'], $semesters);

        $semester_data = [];
        preg_match_all('/value="([0-9]+)-([0-9]+)"/sU', $semesters[1][0], $semester_data);

        return Enums\Semester::createFromPreg($semester_data);
    }

    protected function autorefetch($endpoint, $refetch)
    {
        if (
            array_key_exists($endpoint, $this->fetched_data) &&
            $this->fetched_data[$endpoint] &&
            !$refetch
        ) {
            return $this->fetched_data[$endpoint];
        }

        if (
            !array_key_exists($endpoint, $this->fetched_html) ||
            $this->fetched_html == null ||
            $refetch
        ) {
            $this->fetch($endpoint);
        }
    }

    protected function fetch($endpoint)
    {

        $resp = $this->guzzleClient->request('GET', $endpoint, [], [
            "allow_redirects" => false
        ]);

        $this->fetched_html[$endpoint] = $resp->getBody();
    }
}
