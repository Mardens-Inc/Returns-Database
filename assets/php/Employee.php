<?php

namespace ReturnsDatabase;

use Exception;

class Employee
{
    /**
     *
     */
    public int $employee_id;
    /**
     * @var string The first name of the employee.
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     */
    public string $first_name;
    /**
     * @var string $last_name The last name of the employee. This field can be null if the last name is not provided.
     */
    public string $last_name;
    /**
     * The location of the employee.
     *
     * @var string
     */
    public string $location;

    /**
     * Constructor for creating a new employee object.
     *
     * @param string $employee_id The employee ID of the employee.
     * @param string $first_name The first name of the employee.
     * @param string $last_name The last name of the employee.
     * @param string $location The location of the employee.
     * @return void
     */
    function __construct(string $employee_id, string $first_name, string $last_name, string $location)
    {
        $this->employee_id = $employee_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->location = $location;
    }

    /**
     * Creates a new Employee object from an associative array representing a row in the database.
     *
     * @param array $row The array containing the row data.
     * @return Employee The created Employee object.
     */
    static function fromJson(array $row): Employee
    {
        return new Employee($row['id'] ?? -1, $row['first_name'], $row['last_name'], $row['location']);
    }

    /**
     * Retrieves an Employee object by their ID.
     *
     * @param int $employee_id The ID of the employee.
     * @return Employee|null The Employee object, or null if no employee was found.
     */
    public static function byId(int $employee_id): ?Employee
    {
        $db = Connection::connect();
        $statement = $db->prepare("SELECT * FROM `employees` WHERE id = ? LIMIT 1");
        $statement->bind_param("i", $employee_id);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows == 0) return null;
        return self::fromJson($result->fetch_assoc());
    }

    /**
     * Retrieves all employees from the database.
     *
     * @return array An array containing all employee objects.
     */
    public static function getAll(): array
    {
        $db = Connection::connect();
        $results = $db->query("SELECT * FROM `employees`");
        $employees = [];
        while ($row = $results->fetch_assoc()) {
            $employees[] = self::fromJson($row);
        }
        return $employees;
    }

}