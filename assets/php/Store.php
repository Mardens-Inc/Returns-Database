<?php


namespace ReturnsDatabase;
require_once 'IDatabaseItem.php';
require_once "Connection.php";

class Store implements IDatabaseItem
{

    public int $id;
    public string $city;
    public string $address;


    /**
     * @param int $id
     * @param string $city
     * @param string $address
     */
    public function __construct(int $id, string $city, string $address)
    {
        $this->id = $id;
        $this->city = $city;
        $this->address = $address;
    }

    public static function from_json(array $row): Store
    {
        return new Store($row['id'], $row['city'], $row['address']);
    }

    public static function by_id(int $id): ?Store
    {
        $connection = Connection::connect();
        $result = $connection->query("SELECT * FROM stores WHERE id = $id");
        if ($result->num_rows == 0) {
            return null;
        }
        $row = $result->fetch_assoc();
        return self::from_json($row);
    }

    public static function search(string $query): array
    {
        $connection = Connection::connect();
        $result = $connection->query("SELECT * FROM stores WHERE city LIKE '%$query%' OR address LIKE '%$query%'");
        $stores = [];
        while ($row = $result->fetch_assoc()) {
            $stores[] = self::from_json($row);
        }
        return $stores;
    }

    public static function all(): array
    {
        $connection = Connection::connect();
        $result = $connection->query("SELECT * FROM stores");
        $stores = [];
        while ($row = $result->fetch_assoc()) {
            $stores[] = self::from_json($row);
        }
        return $stores;
    }

    public function save(): void
    {
        if ($this->exists() != -1) {
            self::update();
            return;
        }
        $connection = Connection::connect();
        $connection->query("INSERT INTO stores (city, address) VALUES ('$this->city', '$this->address')");
        $this->id = $connection->insert_id;
    }

    public function delete(): void
    {
        $connection = Connection::connect();
        $connection->query("DELETE FROM stores WHERE id = $this->id");
    }

    public function update(): void
    {
        $stmt = Connection::connect()->prepare("UPDATE stores SET city = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssi", $this->city, $this->address, $this->id);
        $stmt->execute();
    }

    public function __toString(): string
    {
        return "Store: $this->city, $this->address";
    }

    public function __toArray(): array
    {
        return (array)$this;
    }

    public static function empty(): Store
    {
        return new Store(-1, "", "");
    }

    public function exists(): int
    {
        $connection = Connection::connect();
        $result = $connection->prepare("SELECT * FROM stores WHERE city = ? LIMIT 1");
        $result->bind_param("s", $this->city);
        $result->execute();
        $result = $result->get_result();
        return $result->num_rows > 0 ? $this->id = $result->fetch_assoc()['id'] : -1;
    }

    public function is_empty(): bool
    {
        return $this->id == -1;
    }
}