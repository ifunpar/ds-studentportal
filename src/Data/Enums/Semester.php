<?php
namespace Chez14\Desso\Services\Data\Enums;

class Semester {
    protected
        $year,
        $period;
    
    public function __construct($year, $period) {
        $this->year = $year;
        $this->period = $period;
    }

    public function getUrl() {
        return $this->year . "/" . $this->period;
    }

    public static function createFromPreg($pregData) {
        $semesters = [];
        for($i=0; $i<count($pregData[1]); $i++){
            $semesters[] = new self($pregData[1][$i], $pregData[2][$i]);
        }

        return $semesters;
    }
}