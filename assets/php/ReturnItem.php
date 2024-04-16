<?php

namespace ReturnsDatabase;

require_once "ReturnType.php";
require_once "Employee.php";
require_once "ReturnLocation.php";
require_once "ReturnCustomerAddress.php";
require_once "GiftCard.php";
require_once "Connection.php";

use DateTime;
use Exception;

class ReturnItem
{
    public int $id;
    public DateTime $date;
    public string $first_name;
    public string $last_name;
    public ReturnType $type;
    public ?GiftCard $card;
    public Employee $employee;
    public ReturnLocation $store;
    public ReturnCustomerAddress $customerAddress;
    public string $phone;
    public string $email;

    public function __construct(int $id, DateTime $date, string $first_name, string $last_name, ReturnType $type, ?GiftCard $card, Employee $employee, ReturnLocation $store, ReturnCustomerAddress $customerAddress, string $phone, string $email)
    {
        $this->id = $id;
        $this->date = $date;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->type = $type;
        $this->card = $card;
        $this->employee = $employee;
        $this->store = $store;
        $this->customerAddress = $customerAddress;
        $this->phone = $phone;
        $this->email = $email;
    }


    public static function fromJson(array $json): ReturnItem
    {
        $card = $json["card"];
        $emp = $json["employee"];
        $loc = $json["store"];
        $addr = $json["customerAddress"] ?? $json["customer_addr"];

        if ($card != null) {
            if (is_numeric($card)) {
                $card = GiftCard::byId(intval($card));
            } else {
                $card = GiftCard::fromJson($card);
            }
        }

        if (is_numeric($emp)) {
            $emp = Employee::byId(intval($emp));
        } else {
            throw new Exception("Employee must be an integer");
        }

        if (is_numeric($loc)) {
            $loc = ReturnLocation::byId(intval($loc));
        } else {
            $loc = ReturnLocation::fromJson($loc);
        }

        if (is_numeric($addr)) {
            $addr = ReturnCustomerAddress::byId(intval($addr));
        } else {
            $addr = ReturnCustomerAddress::fromJson($addr);
        }

        return new ReturnItem(
            $json['id'] ?? -1,
            new DateTime($json['date']),
            $json['first_name'],
            $json['last_name'],
            ReturnType::parse($json['type']),
            $card,
            $emp,
            $loc,
            $addr,
            $json['phone'],
            $json['email']
        );
    }

    public static function byId(int $id): ?ReturnItem
    {
        $connection = Connection::connect();
        $stmt = $connection->prepare("SELECT * FROM `returns` WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            return null;
        }
        try {
            return ReturnItem::fromJson($result->fetch_assoc());
        } catch (Exception) {
            return null;
        }
    }


    public static function getAll(): array
    {
        $connection = Connection::connect();
        $results = $connection->query("SELECT * FROM `returns`");
        $returns = [];
        while ($row = $results->fetch_assoc()) {
            try {
                $returns[] = ReturnItem::fromJson($row);
            } catch (Exception) {
                continue;
            }
        }
        return $returns;
    }

    public static function search(string $query): array
    {
        $connection = Connection::connect();
        $stmt = $connection->prepare("SELECT * FROM `returns` WHERE first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?");
        $query = "%$query%";
        $stmt->bind_param("sss", $query, $query, $query);
        $stmt->execute();
        $results = $stmt->get_result();
        $returns = [];
        while ($row = $results->fetch_assoc()) {
            try {
                $returns[] = ReturnItem::fromJson($row);
            } catch (Exception) {
                continue;
            }
        }
        return $returns;
    }


    public static function template(): ReturnItem
    {

        $emp = new Employee(-1, "", "", "");
        $loc = new ReturnLocation(-1, "", "");
        $card = new GiftCard(-1, new DateTime(), 0.00, "");
        $cust = new ReturnCustomerAddress(-1, "", "", "", "");
        return new ReturnItem(-1, new DateTime(), "", "", ReturnType::NoReceiptMRC, $card, $emp, $loc, $cust, "", "");
    }

    public function insert(): bool
    {
        if ($this->card != null && $this->card->id == -1)
            $this->card->insert();
        if ($this->store->id == -1)
            $this->store->insert();
        if ($this->customerAddress->id == -1)
            $this->customerAddress->insert();

        $exists = $this->checkIfExists();
        if ($exists) {
            $this->id = $exists->id;
            return true;
        }

        $connection = Connection::connect();
        $stmt = $connection->prepare("INSERT INTO `returns` (first_name, last_name, type, card, employee, store, customer_addr, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            throw new Exception("Failed to prepare the statement");
        }

        $typeNumber = $this->type->value;
        $stmt->bind_param("ssiiiiiss", $this->first_name, $this->last_name, $typeNumber, $this->card->id, $this->employee->employee_id, $this->store->id, $this->customerAddress->id, $this->phone, $this->email);

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute the statement");
        }

        $this->id = $stmt->insert_id;

        return $stmt->affected_rows > 0;
    }


    public static function clear(?DateTime $olderThan = null): int
    {
        $connection = Connection::connect();

        if ($olderThan) {
            $size = $connection->query("SELECT COUNT(*) FROM `returns`")->fetch_row()[0];
            $connection->query("TRUNCATE TABLE `returns`");
            return $size;
        } else {
            $stmt = $connection->prepare("DELETE FROM `returns` WHERE date < ?");
            $formatted = $olderThan->format("Y-m-d H:i:s");
            $stmt->bind_param("s", $formatted);
            $stmt->execute();
            return $stmt->affected_rows;
        }
    }

    public function checkIfExists(): false|ReturnItem
    {
        $connection = Connection::connect();
        $stmt = $connection->prepare("SELECT * FROM `returns` WHERE first_name = ? AND last_name = ? AND type = ? AND card = ? AND employee = ? AND store = ? AND customer_addr = ? AND phone = ? AND email = ? LIMIT 1");
        $typeNumber = $this->type->value;
        $stmt->bind_param("ssiiiiiss", $this->first_name, $this->last_name, $typeNumber, $this->card->id, $this->employee->employee_id, $this->store->id, $this->customerAddress->id, $this->phone, $this->email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? self::fromJson($result->fetch_assoc()) : false;
    }

}