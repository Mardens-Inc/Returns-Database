<?php

namespace ReturnsDatabase;

class DrivesLicense
{
    public string $code;
    public string $drivers_license_number;
    public string $registered_state;
    public string $first_name;
    public string $middle_initials;
    public string $last_name;
    public string $address;
    public string $city;
    public string $state;
    public string $zip;
    public string $date_of_birth;
    public string $gender;

    public function __construct(string $code)
    {
        $this->code = $code;
        $this->drivers_license_number = self::extractString($code, "DAQ", "DCS");
        $this->registered_state = self::extractString($code, "DAJ", "DAK");
        $this->first_name = self::extractString($code, "DDENDAC", "DDFNDAD");
        $this->middle_initials = self::extractString($code, "DDFNDAD", "DDGND");
        $this->last_name = self::extractString($code, "DCS", "DDENDAC");
        $this->address = self::extractString($code, "DAG", "DAI");
        $this->city = self::extractString($code, "DAI", "DAJ");
        $this->state = self::extractString($code, "DAJ", "DAK");
        $this->zip = self::extractString($code, "DAK", "DCF");
        $this->date_of_birth = self::extractDate(self::extractString($code, "DBB", "DBA"));
        $this->gender = self::determineGender(self::extractString($code, "DBC", "DAU"));
    }

    private function determineGender($code)
    {
        return $code === "1" ? "M" : ($code === "2" ? "F" : "");
    }

    private function extractDate($input)
    {
        $month = intval(substr($input, 0, 2));
        $day = intval(substr($input, 2, 2));
        $year = intval(substr($input, 4));
        return date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
    }

    private function extractString($input, $startMarker, $endMarker)
    {
        $startIndex = strpos($input, $startMarker);
        $endIndex = strpos($input, $endMarker, $startIndex + strlen($startMarker));
        if ($startIndex === false || $endIndex === false) return "";
        return substr($input, $startIndex + strlen($startMarker), $endIndex - $startIndex - strlen($startMarker));
    }

    private function filterAlphaNumeric($input)
    {
        return preg_replace("/[^a-zA-Z0-9]/", '', $input);
    }

    /**
     * Convert the object to an array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        $arr = (array)$this;
        unset($arr["code"]);
        return $arr;
    }

}