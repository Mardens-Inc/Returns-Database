<?php

namespace ReturnsDatabase;
require_once "IDatabaseItem.php";

use Exception;
use mysqli;

class Employee implements IDatabaseItem
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public string $location;
    private mysqli $connection;

    /**
     * Constructor for creating a new employee object.
     *
     * @param int $id The employee ID of the employee.
     * @param string $first_name The first name of the employee.
     * @param string $last_name The last name of the employee.
     * @param string $location The location of the employee.
     * @return void
     */
    function __construct(int $id, string $first_name, string $last_name, string $location)
    {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->location = $location;
        $this->connection = Connection::connect();
    }


    public static function from_json(array $row): Employee
    {
        return new Employee($row['id'] ?? -1, $row['first_name'], $row['last_name'], $row['location']);
    }

    public static function by_id(int $id): ?Employee
    {
        $connection = Connection::connect();
        $result = $connection->query("SELECT * FROM employees WHERE id = $id");
        if ($result->num_rows == 0) {
            return null;
        }
        $row = $result->fetch_assoc();
        return self::from_json($row);
    }

    public static function search(string $query, int $limit, int $offset, string $sort_column, bool $ascending): array
    {
        $sort_column = strtolower($sort_column);
        $sort_column = in_array($sort_column, ['id', 'first_name', 'last_name', 'location']) ? $sort_column : 'id';
        $ascending = $ascending ? 'ASC' : 'DESC';
        $connection = Connection::connect();
        $result = $connection->prepare("SELECT * FROM employees WHERE first_name LIKE ? OR last_name LIKE ? OR location LIKE ? OR concat(first_name, ' ', last_name) LIKE ? ORDER BY $sort_column $ascending LIMIT $limit OFFSET $offset");
        $query = "%$query%";
        $result->bind_param("ssss", $query, $query, $query, $query);
        $result->execute();
        $result = $result->get_result();
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = self::from_json($row);
        }
        return $employees;
    }

    public static function range(int $limit, int $offset, string $sort_column, bool $ascending): array
    {
        $connection = Connection::connect();
        $sort_column = strtolower($sort_column);
        $sort_column = in_array($sort_column, ['id', 'first_name', 'last_name', 'location']) ? $sort_column : 'id';
        $ascending = $ascending ? 'ASC' : 'DESC';
        $result = $connection->query("SELECT * FROM employees ORDER BY $sort_column $ascending LIMIT $limit OFFSET $offset");
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = self::from_json($row);
        }
        return $employees;
    }

    /**
     * @throws Exception
     */
    public function save(): void
    {
        throw new Exception("Please use the employee api for modify the employee data.");
    }

    public function delete(): void
    {
        throw new Exception("Please use the employee api for modify the employee data.");
    }

    /**
     * @throws Exception
     */
    public function update(): void
    {
        throw new Exception("Please use the employee api for modify the employee data.");
    }

    public function exists(): int
    {
        if (self::is_empty()) return -1;
        $result = $this->connection->prepare("SELECT id FROM employees WHERE first_name = ? AND last_name = ?");
        $result->bind_param("ss", $this->first_name, $this->last_name);
        $result->execute();
        $result = $result->get_result();
        return $result->num_rows > 0 ? $this->id = $result->fetch_assoc()['id'] : -1;
    }

    public static function empty(): Employee
    {
        return new Employee(-1, "", "", "");
    }

    public function is_empty(): bool
    {
        return $this === self::empty();
    }

    public function __toString(): string
    {
        return "Employee: $this->first_name $this->last_name";
    }

    public function jsonSerialize(): array
    {
        return (array)$this;
    }

    public function reload_from_database(): Employee
    {
        if (self::exists() == -1) return $this;
        $employee = self::by_id($this->id);
        $this->first_name = $employee->first_name;
        $this->last_name = $employee->last_name;
        $this->location = $employee->location;
        return $this;
    }

    public static function count(): int
    {
        $result = Connection::connect()->query("SELECT COUNT(*) FROM employees");
        return $result->fetch_assoc()["COUNT(*)"];
    }
}