<?php

namespace ReturnsDatabase;

use Exception;

class ReturnCustomerAddress
{
    /**
     * @var integer $id
     */
    public int $id;
    /**
     * @var string $street The street name.
     */
    public string $street;
    /**
     * @var string $city The city name
     */
    public string $city;
    /**
     * @var string $state The state value
     */
    public string $state;

    /**
     * Class constructor.
     *
     * @param int $id The ID of the object.
     * @param string $street The street of the address.
     * @param string $city The city of the address.
     * @param string $state The state of the address.
     * @return void
     */
    public function __construct(int $id, string $street, string $city, string $state)
    {
        $this->id = $id;
        $this->street = $street;
        $this->city = $city;
        $this->state = $state;
    }

    /**
     * Creates a ReturnCustomerAddress object from a database row.
     *
     * @param array $json An array representing a database row
     * @return ReturnCustomerAddress The ReturnCustomerAddress object
     */
    static function fromJson(array $json): ReturnCustomerAddress
    {
        return new ReturnCustomerAddress($json['id'] ?? -1, $json['street'], $json['city'], $json['state']);
    }

    /**
     * Inserts a ReturnCustomerAddress into the database.
     *
     * @return bool Returns true if the insertion was successful, false otherwise.
     * @throws Exception Throws an exception if the statement preparation or execution fails.
     */
    public function insert(): bool
    {
        $item = $this->checkIfExists();
        if($item) {
            $this->id = $item->id;
            return true;
        }
        $connection = Connection::connect();
        $stmt = $connection->prepare("INSERT INTO `returns_addr` (street, city, state) VALUES (?, ?, ?)");

        if ($stmt === false) {
            throw new Exception("Failed to prepare the statement");
        }

        $stmt->bind_param("sss", $this->street, $this->city, $this->state);

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute the statement");
        }
        $this->id = $stmt->insert_id;

        return $stmt->affected_rows > 0;
    }

    /**
     * Retrieves a ReturnCustomerAddress by its ID.
     *
     * @param int $id The ID of the ReturnCustomerAddress
     * @return ReturnCustomerAddress The ReturnCustomerAddress object
     */
    public static function byId(int $id): ReturnCustomerAddress
    {
        $db = Connection::connect();
        $stmt = $db->prepare("SELECT * FROM returns_addr WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return ReturnCustomerAddress::fromJson((array)$row);
    }

    /**
     * Retrieve all return customer addresses from the database.
     *
     * @return array An array of ReturnCustomerAddress objects.
     */
    public static function getAll(): array
    {
        $db = Connection::connect();
        $results = $db->query("SELECT * FROM `returns_addr`");
        $addresses = [];
        foreach ($results as $row) {
            $addresses[] = ReturnCustomerAddress::fromJson($row);
        }
        return $addresses;
    }

    /**
     * Checks if the ReturnCustomerAddress exists in the database.
     *
     * @return ReturnCustomerAddress|false The ReturnCustomerAddress object if it exists, false otherwise
     */
    public function checkIfExists(): ReturnCustomerAddress|false
    {
        $db = Connection::connect();
        $stmt = $db->prepare("SELECT * FROM returns_addr WHERE street = ? AND city = ? AND state = ? LIMIT 1");
        $stmt->bind_param('sss', $this->street, $this->city, $this->state);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? ReturnCustomerAddress::fromJson($result->fetch_assoc()) : false;
    }


}