<?php

namespace EmployeeList;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Employee
{
    public int $id;
    public string $employee_id;
    public string $first_name;
    public string $last_name;
    public string $location;

    function __construct(int $id, string $employee_id, string $first_name, string $last_name, string $location)
    {
        $this->id = $id;
        $this->employee_id = $employee_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->location = $location;
    }
}

class Employees
{

    private \mysqli $connection;

    function __construct()
    {
        require_once "connections.inc.php";
        $this->connection = \Connection::connect();
    }

    public function add(Employee $employee): bool
    {
        $employee_id = $employee->employee_id;
        $first_name = $employee->first_name;
        $last_name = $employee->last_name;
        $location = $employee->location;
        $stmt = $this->connection->prepare("INSERT INTO `employees` (employee_id, first_name, last_name, location) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $employee_id, $first_name, $last_name, $location);
        return $stmt->execute();
    }

    public function get(): array
    {
        $result = $this->connection->query("SELECT * FROM `employees`");
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employee = new Employee($row["id"], $row["employee_id"], $row["first_name"], $row["last_name"], $row["location"]);
            $employees[] = $employee;
        }
        return $employees;
    }

    public function search(string $query): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM `employees` WHERE first_name LIKE ? OR last_name LIKE ? OR employee_id = ? OR CONCAT(LOWER(first_name), ' ', LOWER(last_name)) LIKE ? OR location LIKE ? ORDER BY location");
        $like = "%$query%";
        $stmt->bind_param("sssss", $like, $like, $query, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employee = new Employee($row["id"], $row["employee_id"], $row["first_name"], $row["last_name"], $row["location"]);
            $employees[] = $employee;
        }
        return $employees;
    }

    /**
     * Imports data from a file of a specified type.
     *
     * @param string $type The type of the file (csv, json, xlsx).
     * @param mixed $file The file to import the data from.
     * @return array The imported data.
     */
    public function import(string $type, mixed $file): array
    {
        $this->connection->query("TRUNCATE TABLE `employees`");
        $reader = new Xlsx();
        $spreadsheet = $reader->load($file["tmp_name"]);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        array_shift($rows);
        $count = 0;
        foreach ($rows as $row) {
            $employee = new Employee(0, $row[0], $row[1], $row[2], $row[3]);
            $this->add($employee);
            $count++;
        }
        return ["success" => true, "count" => $count];
    }


}