<?php

namespace ReturnsDatabase;

use DateInterval;
use DateTime;
use Exception;

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
    public string $issued_date;
    public string $expiration_date;
    public string $height;
    public string $eye_color;

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
        $this->zip = substr(self::extractString($code, "DAK", "DCF"), 0, 5);
        $this->date_of_birth = self::extractDate(self::extractString($code, "DBB", "DBA"));
        $this->issued_date = self::extractDate(self::extractString($code, "DBD", "DBB"));
        $this->expiration_date = self::extractDate(self::extractString($code, "DBA", "DBC"));
        $this->eye_color = self::extractString($code, "DAY", "DAG");
        $this->gender = self::determineGender(self::extractString($code, "DBC", "DAU"));
        $this->height = $this->calculateHeight(self::extractString($code, "DAU", "DAY"));
    }

    /**
     * Determine the gender based on the given code.
     *
     * @param string $code The code representing the gender.
     *
     * @return string The gender as a string.
     */
    private function determineGender(string $code): string
    {
        $genderCode = intval($code);
        if ($genderCode == 1) {
            return "Male";
        }
        if ($genderCode == 2) {
            return "Female";
        }
        if ($genderCode == 9) {
            return "Other";
        }
        return "Not Specified";
    }

    private function calculateHeight(string $code): string
    {
        if (str_ends_with($code, "IN")) {
            $heightCode = intval(substr($code, 0, -3));
            $feet = floor($heightCode / 12);
            $inches = $heightCode % 12;
            return $feet . "ft " . $inches . "in";
        }
        if (str_ends_with($code, "CM")) {
            $heightCode = intval(substr($code, 0, -3));
            $feet = floor($heightCode / 30.48);
            $inches = round(($heightCode % 30.48) / 2.54);
            return $feet . "ft " . $inches . "in";
        }
        return "Not Specified";
    }

    /**
     * Extracts the date from the input string and returns it in the format Y-m-d.
     *
     * @param string $input The input string containing the date.
     * @return string The extracted date in the format Y-m-d.
     */
    private function extractDate(string $input): string
    {
        $month = intval(substr($input, 0, 2));
        $day = intval(substr($input, 2, 2));
        $year = intval(substr($input, 4));
        return date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
    }

    /**
     * Extracts a string from the given input string between the start marker and end marker.
     *
     * @param string $input The input string to extract the substring from.
     * @param string $startMarker The start marker for the substring.
     * @param string $endMarker The end marker for the substring.
     * @return string The extracted substring, or an empty string if the markers are not found.
     */
    private function extractString(string $input, string $startMarker, string $endMarker): string
    {
        $startIndex = strpos($input, $startMarker);
        $endIndex = strpos($input, $endMarker, $startIndex + strlen($startMarker));
        if ($startIndex === false || $endIndex === false) return "";
        return substr($input, $startIndex + strlen($startMarker), $endIndex - $startIndex - strlen($startMarker));
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

    private function encodeBase64(string $input): string
    {
        return base64_encode($input);
    }

}