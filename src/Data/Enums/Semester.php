<?php

namespace Desso\Services\Data\Enums;

/**
 * Semester Model and Parsing Toolkit
 * 
 * This class will handle following task:
 *   - Parse PregResponse and return Semesters Representation of that datas.
 *   - Generate URL for the following Semester.
 */
class Semester
{
    protected
        $year,
        $period;

    /**
     * Create new Semester Representation for some endpoints.
     * 
     * @see \Desso\Services\Data\Nilai::getSemesters() Get Semester Lists
     *
     * @param integer $year Full year number of the period (2020, 2019, 2018, etc...)
     * @param integer $period Period code representing the periode. Following
     * numbers are supported: 1 - Ganjil; 2 - Genap; 3 - Semester Pendek.
     */
    public function __construct(int $year, int $period)
    {
        $this->year = $year;
        $this->period = $period;
    }

    /**
     * Generate Postfix URL for this semester.
     * 
     * This function will return something like `2020/1`
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->year . "/" . $this->period;
    }

    /**
     * Parse Semester from PredData from Nilai Class.
     * 
     * Only used by Nilai Class to create Semester Arrays.
     * 
     *  @see \Desso\Services\Data\Nilai::getSemesters() Get Semester Lists
     *
     * @param array $pregData
     * @return Semester[] Semester Representations of current url.
     */
    public static function createFromPreg($pregData): array
    {
        $semesters = [];
        for ($i = 0; $i < count($pregData[1]); $i++) {
            $semesters[] = new self($pregData[1][$i], $pregData[2][$i]);
        }

        return $semesters;
    }
}
