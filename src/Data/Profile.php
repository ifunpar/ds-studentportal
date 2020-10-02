<?php

namespace Desso\Services\Data;


class Profile
{
    protected
        $guzzleClient,
        $config;

    protected
        $profile_image = '/<img src="data:image\/(jpeg|png);base64,([A-Za-z0-9+\/=]+)"/m',
        $data_name = '/<h4 class="my-0".*><b>(.*)<\/b>/mU',
        $data_npm = '/<h5 class="my-0"><small>(.*)<\/small>/mU',
        $data_jurusan = '/<h5 class=\'text\.muted my-0\'><small>(.*)<\/small>/mU',
        $data_fakultas = '/<h5 class=\'text\.muted my-0 mb-3\'><small>(.*)<\/small>/mU',
        $data_doli = '/<h4 class=\'my-0\'><small>(.*)<\/small>/mU',
        $data_doli_email = '/<h5 class=\'text\.muted\'><small>(.*)<\/small>/mU',
        $datas = '/<h8 class=\'m-0\'>(.*)<\/h8>/mU',
        $data_lahir = '/<h8 class=\'m-0\'>(.*)<\/h8><h8>, (.*)<\/h8>/mU';
    /**
     * Catetan Mini buat NPM:
     * 
     * Bentuk datanya adalah:
     *   Program | NPM
     * 
     * Contoh:
     *   Sarjana | 2010110001
     */
    protected $datas_pointkey = [
        "sex",
        "born_city",
        "religion",
        "address",
        "post_code",
        "phone",
        "marriage",
        "address_status",
        "nationality",
        "money_source",
        "father_name",
        "mother_name",
        "guardian_name",
        "parent_phone",
        "father_occupation",
        "mother_occupation",
        "father_education",
        "mother_education",
        "parent_address",
        "parent_city",
        "parent_post_code"
    ];
    /**
     * Catetan mini buat daftar data:
     *  0 Jenis kelamin
     *  1 Tempat lahir
     *  2 Agama
     *  3 Alamat
     *  4 Kode pos
     *  5 No Telepon / HP
     *  6 Status perkawinan
     *  7 Status alamat
     *  8 Status warga negara
     *  9 Sumber biaya
     * 10 Nama ayah
     * 11 Nama ibu
     * 12 Nama wali
     * 13 Nomer Telepon
     * 14 Pekerjaan Ayah
     * 15 Pekerjaan Ibu
     * 16 Pendidikan Ayah
     * 17 Pendidikan Ibu
     * 18 Alamat Ortu/Wali
     * 19 Kota (ortu?)
     * 20 Kode Pos (ortu?)
     */

    private
        $fetched_data,
        $fetched_html;

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

        $this->fetch();
    }

    /**
     * @param false $refetch
     * @return mixed
     */
    public function getProfilePict_base64($refetch = false)
    {
        if ($this->fetched_data && !$refetch) {
            return $this->fetched_data;
        }

        if ($this->fetched_html == null || $refetch) {
            $this->fetch();
        }

        $matched_pict = [];
        preg_match_all($this->profile_image, $this->fetched_html, $matched_pict);
        return $matched_pict[2][0];
    }

    /**
     * @param false $refetch
     * @return array
     */
    public function getDatas($refetch = false) : array
    {
        if ($this->fetched_data && !$refetch) {
            return $this->fetched_data;
        }

        if ($this->fetched_html == null || $refetch) {
            $this->fetch();
        }


        $datas = [];

        /**
         * DATAS
         */
        $matched_datas = [];
        preg_match_all($this->datas, $this->fetched_html, $matched_datas);
        for ($i = 0; $i < count($this->datas_pointkey); $i++) {
            $datas[$this->datas_pointkey[$i]] = $matched_datas[1][$i];
        }

        /**
         * NAMA
         */
        $matched_data_name = [];
        preg_match_all($this->data_name, $this->fetched_html, $matched_data_name);
        $datas['name'] = $matched_data_name[1][0];

        /**
         * NPM
         */
        $matched_data_npm = [];
        preg_match_all($this->data_npm, $this->fetched_html, $matched_data_npm);
        $dax = explode(" | ", $matched_data_npm[1][0]);
        $datas['npm'] = $dax[1];
        $datas['program'] = $dax[0];

        /**
         * JURUSAN
         */
        $matched_data_jurusan = [];
        preg_match_all($this->data_jurusan, $this->fetched_html, $matched_data_jurusan);
        $datas['jurusan'] = $matched_data_jurusan[1][0];

        /**
         * FAKULTAS
         */
        $matched_data_fakultas = [];
        preg_match_all($this->data_fakultas, $this->fetched_html, $matched_data_fakultas);
        $datas['fakultas'] = $matched_data_fakultas[1][0];

        /**
         * DOLI
         */
        $matched_data_doli = [];
        preg_match_all($this->data_doli, $this->fetched_html, $matched_data_doli);
        $datas['doli'] = $matched_data_doli[1][0];

        /**
         * DOLI_EMAIL
         */
        $matched_data_doli_email = [];
        preg_match_all($this->data_doli_email, $this->fetched_html, $matched_data_doli_email);
        $datas['doli_email'] = $matched_data_doli_email[1][0];

        /**
         * LAHIR
         */
        $matched_data_lahir = [];
        preg_match_all($this->data_lahir, $this->fetched_html, $matched_data_lahir);
        $datas['born_date'] = $matched_data_lahir[2][0];

        /**
         * DONE
         */
        $this->fetched_data = $datas;
        return $datas;
    }

    /**
     * @return void
     */
    protected function fetch()
    {

        $resp = $this->guzzleClient->request('GET', '/profil', [], [
            "allow_redirects" => false
        ]);

        $this->fetched_html = $resp->getBody();
    }
}
