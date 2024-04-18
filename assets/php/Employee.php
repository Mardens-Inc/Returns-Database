<?php

namespace ReturnsDatabase;

use Exception;

class Employee implements IDatabaseItem
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public string $location;

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
    }


    public static function from_json(array $row): Employee
    {
        return new Employee($row['id'], $row['first_name'], $row['last_name'], $row['location']);
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

    public static function search(string $query): array
    {
        $connection = Connection::connect();
        $result = $connection->prepare("SELECT * FROM employees WHERE first_name LIKE ? OR last_name LIKE ? OR location LIKE ? OR concat(first_name, ' ', last_name) LIKE ?");
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

    public static function all(): array
    {
        $connection = Connection::connect();
        $result = $connection->query("SELECT * FROM employees");
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
        $result = Connection::connect()->prepare("SELECT id FROM employees WHERE first_name = ? AND last_name = ?");
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
        return $this->id == -1;
    }

    public function __toString(): string
    {
        return "Employee: $this->first_name $this->last_name";
    }

    public function __toArray(): array
    {
        return (array)$this;
    }
}