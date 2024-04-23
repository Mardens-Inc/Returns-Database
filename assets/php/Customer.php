<?php

namespace ReturnsDatabase;

use Exception;
use mysqli;

class Customer implements IDatabaseItem
{
    public int $id;
    public string $city;
    public string $address;
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $phone;
    public string $zip;
    public string $state;
    public string $date_of_birth;
    private mysqli $connection;

    /**
     * Class constructor.
     *
     * @param int $id The ID of the object.
     * @param string $city The city of the object.
     * @param string $address The address of the object.
     * @param string $first_name The first name of the object.
     * @param string $last_name The last name of the object.
     * @param string $email The email address of the object.
     * @param string $phone_number The phone number of the object.
     * @param string $zip The ZIP code of the object.
     * @param string $state The state of the object.
     * @param string $date_of_birth The date of birth of the object.
     */
    public function __construct(int $id, string $city, string $address, string $first_name, string $last_name, string $email, string $phone_number, string $zip, string $state, string $date_of_birth)
    {
        $this->id = $id;
        $this->city = $city;
        $this->address = $address;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone = $phone_number;
        $this->zip = $zip;
        $this->state = $state;
        $this->date_of_birth = $date_of_birth;
        $this->connection = Connection::connect();
    }


    public static function from_json(array $row): Customer
    {
        return new Customer($row['id'] ?? -1, $row['city'], $row['address'], $row['first_name'], $row['last_name'], $row['email'], $row['phone'], $row['zip'], $row['state'], $row['date_of_birth']);
    }

    public static function by_id(int $id): ?Customer
    {
        $result = Connection::connect()->query("SELECT * FROM customers WHERE id = $id LIMIT 1");
        return $result->num_rows == 0 ? null : self::from_json($result->fetch_assoc());
    }

    public static function search(string $query, int $limit, int $offset, string $sort_column, bool $ascending): array
    {
        $connection = Connection::connect();
        $query = "%$query%";

        $ascending = $ascending ? 'ASC' : 'DESC';
        $sort_column = strtolower($sort_column);
        $sort_column = in_array($sort_column, ['id', 'city', 'address', 'first_name', 'last_name', 'email', 'phone', 'zip', 'state', 'date_of_birth']) ? $sort_column : 'id';

        $result = $connection->prepare("SELECT * FROM customers WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR zip LIKE ? OR state LIKE ? OR date_of_birth LIKE ? OR concat(first_name, ' ', last_name) LIKE ? ORDER BY $sort_column $ascending LIMIT $limit OFFSET $offset");
        $result->bind_param("ssssssss", $query, $query, $query, $query, $query, $query, $query, $query);
        $result->execute();
        $result = $result->get_result();
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = self::from_json($row);
        }
        return $customers;
    }

    public static function range(int $limit, int $offset, string $sort_column, bool $ascending): array
    {
        $ascending = $ascending ? 'ASC' : 'DESC';
        $sort_column = strtolower($sort_column);
        $sort_column = in_array($sort_column, ['id', 'city', 'address', 'first_name', 'last_name', 'email', 'phone', 'zip', 'state', 'date_of_birth']) ? $sort_column : 'id';
        $result = Connection::connect()->query("SELECT * FROM customers ORDER BY $sort_column $ascending LIMIT $limit OFFSET $offset");
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = self::from_json($row);
        }
        return $customers;
    }

    public function save(): void
    {
        if ($this->exists() != -1) {
            $this->update();
            return;
        }

        $stmt = $this->connection->prepare("INSERT INTO customers (city, address, first_name, last_name, email, phone, zip, state, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $this->city, $this->address, $this->first_name, $this->last_name, $this->email, $this->phone, $this->zip, $this->state, $this->date_of_birth);
        $stmt->execute();

        $this->id = $this->connection->insert_id;
    }

    public function delete(): void
    {
        $this->connection->query("DELETE FROM customers WHERE id = $this->id");
    }

    public function update(): void
    {
        if ($this->exists() == -1) {
            self::save();
        }
        $stmt = $this->connection->prepare("UPDATE customers SET city = ?, address = ?, first_name = ?, last_name = ?, email = ?, phone = ?, zip = ?, state = ?, date_of_birth = ? WHERE id = ?");
        $stmt->bind_param("sssssssssi", $this->city, $this->address, $this->first_name, $this->last_name, $this->email, $this->phone, $this->zip, $this->state, $this->date_of_birth, $this->id);
        $stmt->execute();
    }

    public function exists(): int
    {
        if (self::is_empty()) return -1;
        $result = $this->connection->prepare("SELECT id FROM customers WHERE first_name = ? AND last_name = ? AND date_of_birth = ? LIMIT 1");
        $result->bind_param("sss", $this->first_name, $this->last_name, $this->date_of_birth);
        $result->execute();
        $result = $result->get_result();
        return $result->num_rows == 0 ? -1 : $this->id = $result->fetch_assoc()['id'];
    }

    public static function empty(): Customer
    {
        return new Customer(-1, "", "", "", "", "", "", "", "", "");
    }

    public function is_empty(): bool
    {
        return $this === self::empty();
    }

    public function __toString(): string
    {
        return "Customer: $this->first_name, $this->last_name, $this->email, $this->phone, $this->zip, $this->state, $this->date_of_birth";
    }

    public function jsonSerialize(): array
    {
        return (array)$this;
    }

    public function reload_from_database(): Customer
    {
        if (self::exists() == -1) return $this;
        $customer = self::by_id($this->id);
        $this->city = $customer->city;
        $this->address = $customer->address;
        $this->first_name = $customer->first_name;
        $this->last_name = $customer->last_name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->zip = $customer->zip;
        $this->state = $customer->state;
        $this->date_of_birth = $customer->date_of_birth;
        return $this;
    }
    public static function count(): int
    {
        $result = Connection::connect()->query("SELECT COUNT(*) FROM customers");
        return $result->fetch_assoc()["COUNT(*)"];
    }
}