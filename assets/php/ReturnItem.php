<?php

namespace ReturnsDatabase;

require_once "ReturnType.php";
require_once "Employee.php";
require_once "Customer.php";
require_once "GiftCard.php";
require_once "Connection.php";

use DateTime;
use Exception;

class ReturnItem implements IDatabaseItem
{
    public int $id;
    public DateTime $date;
    public ReturnType $type;
    public ?GiftCard $card;
    public Employee $employee;
    public Customer $customer;
    public Store $store;

    public function __construct(int $id, DateTime $date, ReturnType $type, ?GiftCard $card, Employee $employee, Customer $customer, Store $store)
    {
        $this->id = $id;
        $this->date = $date;
        $this->type = $type;
        $this->card = $card;
        $this->employee = $employee;
        $this->customer = $customer;
        $this->store = $store;
    }


    /**
     * @throws Exception
     */
    public static function from_json(array $row): ReturnItem
    {
        $employee = $row["employee"];
        $customer = $row["customer"];
        $card = $row["card"];
        $type = $row["type"];
        $store = $row["store"];

        // Parse Employee
        if (is_numeric($employee)) {
            $employee = Employee::by_id($employee);
        } else if (is_array($employee)) {
            $employee = Employee::from_json($employee);
        }
        // Parse Customer
        if (is_numeric($customer)) {
            $customer = Customer::by_id($customer);
        } else if (is_array($customer)) {
            $customer = Customer::from_json($customer);
        } else {
            throw new Exception("Invalid Customer!");
        }
        // Parse GiftCard
        if (is_numeric($card)) {
            $card = GiftCard::by_id($card);
        } else if (is_array($card)) {
            $card = GiftCard::from_json($card);
        } else if (is_string($card)) {
            $card = GiftCard::by_card_number($card);
        } else {
            throw new Exception("Invalid Card!");
        }

        // Parse ReturnType
        if (is_numeric($type)) {
            $type = ReturnType::parse(intval($type));
        } else {
            throw new Exception("Invalid type, type must be a number.");
        }

        // Parse Store
        if (is_numeric($store)) {
            $store = Store::by_id($store);
        } else if (is_array($store)) {
            $store = Store::from_json($store);
        } else {
            throw new Exception("Invalid Store!");
        }

        return new ReturnItem($row['id'], new DateTime($row['date']), $type, $card, $employee, $customer, $store);
    }

    public static function by_id(int $id): ?ReturnItem
    {
        $result = Connection::connect()->query("SELECT * FROM returns WHERE id = $id LIMIT 1");
        try {
            return $result->num_rows == 0 ? null : self::from_json($result->fetch_assoc());
        } catch (Exception) {
            return null;
        }
    }

    public static function search(string $query): array
    {
        $customers = join(',', array_map(function (Customer $customer) {
            return $customer->id;
        }, Customer::search($query)));
        $employees = join(',', array_map(function (Employee $employee) {
            return $employee->id;
        }, Employee::search($query)));
        $gift_cards = join(',', array_map(function (GiftCard $gift_card) {
            return $gift_card->id;
        }, GiftCard::search($query)));
        $stores = join(',', array_map(function (Store $store) {
            return $store->id;
        }, Store::search($query)));


        $result = Connection::connect()->prepare("SELECT * FROM returns WHERE date LIKE ? OR type = ? OR employee IN (?) OR customer IN (?) OR card IN (?) OR store IN (?)");
        $query = "%$query%";
        $result->bind_param("sissss", $query, $query, $employees, $customers, $gift_cards, $stores);
        $result->execute();
        $result = $result->get_result();
        $returns = [];
        while ($row = $result->fetch_assoc()) {
            try {
                $returns[] = self::from_json($row);
            } catch (Exception) {
                continue;
            }
        }
        return $returns;
    }

    public static function all(): array
    {
        $result = Connection::connect()->query("SELECT * FROM returns");
        $returns = [];
        while ($row = $result->fetch_assoc()) {
            try {
                $returns[] = self::from_json($row);
            } catch (Exception) {
                continue;
            }
        }
        return $returns;
    }

    public function save(): void
    {
        if (self::exists() != -1) {
            $this->update();
            return;
        }

        // Update the referenced tables
        $this->card->save();
        $this->customer->save();
        $this->store->save();

        $result = Connection::connect()->prepare("INSERT INTO returns (date, type, card, employee, customer, store) VALUES (?, ?, ?, ?, ?, ?)");
        $date = $this->date->format("Y-m-d H:i:s");
        $type = $this->type->value;
        $result->bind_param("sisiii", $date, $type, $this->card->id, $this->employee->id, $this->customer->id, $this->store->id);
        $result->execute();
    }

    public function delete(): void
    {
        $result = Connection::connect()->prepare("DELETE FROM returns WHERE id = ?");
        $result->bind_param("i", $this->id);
        $result->execute();
    }

    public function update(): void
    {
        // Update the referenced tables
        $this->card->save();
        $this->customer->save();
        $this->store->save();

        $result = Connection::connect()->prepare("UPDATE returns SET date = ?, type = ?, card = ?, employee = ?, customer = ?, store = ? WHERE id = ?");
        $date = $this->date->format("Y-m-d H:i:s");
        $type = $this->type->value;
        $result->bind_param("sisiiii", $date, $type, $this->card->id, $this->employee->id, $this->customer->id, $this->store->id, $this->id);
        $result->execute();
    }

    public function exists(): int
    {
        if (self::is_empty()) return -1;
        $result = Connection::connect()->prepare("SELECT id FROM returns WHERE type = ? AND card = ? AND employee = ? AND customer = ? AND store = ?");
        $type = $this->type->value;
        $result->bind_param("iiiii", $type, $this->card->id, $this->employee->id, $this->customer->id, $this->store->id);
        $result->execute();
        $result = $result->get_result();
        return $result->num_rows > 0 ? $this->id = $result->fetch_assoc()["id"] : -1;
    }

    public static function empty(): ReturnItem
    {
        return new ReturnItem(-1, new DateTime(), ReturnType::parse(0), null, Employee::empty(), Customer::empty(), Store::empty());
    }

    public function is_empty(): bool
    {
        return $this === self::empty();
    }

    public function __toString(): string
    {
        return "Return Item: $this->id, $this->date, $this->type, $this->card, $this->employee, $this->customer, $this->store";
    }

    public function __toArray(): array
    {
        return (array)$this;
    }
}