<?php

namespace ReturnsDatabase;

use Exception;

class ReturnLocation
{
    /**
     * @var int $id The unique identifier for the record
     */
    public int $id;
    /**
     * @var string $city The city of the address.
     */
    public string $city;
    /**
     *
     */
    public string $address;

    /**
     * Class constructor.
     *
     * @param int $id The ID of the object.
     * @param string $city The city of the object.
     * @param string $address The address of the object.
     */
    public function __construct(int $id, string $city, string $address)
    {
        $this->id = $id;
        $this->address = $address;
        $this->city = $city;
    }

    static function fromJson(array $row): ReturnLocation
    {
        return new ReturnLocation($row['id'] ?? -1, $row['city'], $row['address']);
    }

    public static function byId(int $id): ?ReturnLocation
    {
        $db = Connection::connect();
        $stmt = $db->prepare('SELECT * FROM locations WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row === false) {
            return null;
        }
        return self::fromJson((array)$row);
    }

    public static function getAll(): array
    {
        $db = Connection::connect();
        $results = $db->query("SELECT * FROM locations");
        $locations = [];
        while ($row = $results->fetch_assoc()) {
            $locations[] = self::fromJson($row);
        }
        return $locations;
    }

    /**
     * Insert the object into the database.
     *
     * @return bool True if the object was successfully inserted, false otherwise.
     * @throws Exception If preparing or executing the statement fails.
     */
    public function insert(): bool
    {
        $exists = $this->checkIfExists();
        if ($exists) {
            $this->id = $exists->id;
            return true;
        }


        $connection = Connection::connect();
        $stmt = $connection->prepare("INSERT INTO `locations` ( address, city) VALUES (?, ?)");

        if ($stmt === false) {
            throw new Exception("Failed to prepare the statement");
        }

        $stmt->bind_param("ss", $this->address, $this->city);

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute the statement");
        }

        $this->id = $stmt->insert_id;

        return $stmt->affected_rows > 0;
    }

    public function checkIfExists(): false|ReturnLocation
    {
        $connection = Connection::connect();
        $stmt = $connection->prepare("SELECT * FROM locations WHERE address = ? AND city = ? LIMIT 1");
        $stmt->bind_param("ss", $this->address, $this->city);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? self::fromJson($result->fetch_assoc()) : false;
    }
}