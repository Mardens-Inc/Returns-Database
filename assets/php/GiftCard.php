<?php

namespace ReturnsDatabase;

use DateTime;
use Exception;

class GiftCard implements IDatabaseItem
{
    /**
     * @var int $id The unique identifier for the entity.
     */
    public int $id;
    /**
     * @var DateTime $date
     */
    public DateTime $date;
    /**
     * @var float $amount The amount value.
     */
    public float $amount;
    /**
     * @var string $card
     */
    public string $card;

    /**
     * Constructor.
     *
     * @param int $id The ID.
     * @param DateTime $date The date.
     * @param float $amount The amount.
     * @param string $card The card.
     */
    public function __construct(int $id, DateTime $date, float $amount, string $card)
    {
        $this->id = $id;
        $this->date = $date;
        $this->amount = $amount;
        $this->card = $card;
    }


    public static function from_json(array $row): GiftCard
    {
        try {
            return new GiftCard($row['id'], new DateTime($row['date']), $row['amount'], $row['card']);
        } catch (Exception $e) {
            return self::empty();
        }
    }

    public static function by_id(int $id): ?GiftCard
    {
        $result = Connection::connect()->query("SELECT * FROM gift_cards WHERE id = $id");
        if ($result->num_rows == 0) {
            return null;
        }
        $row = $result->fetch_assoc();
        try {
            return self::from_json($row);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function by_card_number(int $card): ?GiftCard
    {
        $result = Connection::connect()->query("SELECT * FROM gift_cards WHERE card = $card");
        if ($result->num_rows == 0) {
            return null;
        }
        $row = $result->fetch_assoc();
        try {
            return self::from_json($row);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function search(string $query): array
    {
        $stmt = Connection::connect()->prepare("SELECT * FROM gift_cards WHERE card LIKE ?");
        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();
        $gift_cards = [];
        while ($row = $result->fetch_assoc()) {
            try {
                $gift_cards[] = self::from_json($row);
            } catch (Exception $e) {
                continue;
            }
        }
        return $gift_cards;
    }

    public static function all(): array
    {
        $result = Connection::connect()->query("SELECT * FROM gift_cards");
        $gift_cards = [];
        while ($row = $result->fetch_assoc()) {
            try {
                $gift_cards[] = self::from_json($row);
            } catch (Exception $e) {
                continue;
            }
        }
        return $gift_cards;
    }

    public function save(): void
    {
        if ($this->exists() != -1) {
            $this->update();
            return;
        }
        $connection = Connection::connect();
        $stmt = $connection->prepare("INSERT INTO gift_cards (date, amount, card) VALUES (?, ?, ?)");
        $date = $this->date->format('Y-m-d H:i:s');
        $stmt->bind_param("sds", $date, $this->amount, $this->card);
        $stmt->execute();
        $this->id = $connection->insert_id;
    }

    public function delete(): void
    {
        if ($this->exists() == -1) {
            throw new Exception("Gift card does not exist.");
        }
        Connection::connect()->query("DELETE FROM gift_cards WHERE id = $this->id");
    }

    public function update(): void
    {
        if ($this->exists() == -1) {
            $this->save();
        }
        $stmt = Connection::connect()->prepare("UPDATE gift_cards SET amount = ?, card = ? WHERE id = ?");
        $stmt->bind_param("dsi", $this->amount, $this->card, $this->id);
        $stmt->execute();
    }

    public function exists(): int
    {
        if ($this->is_empty()) return -1;
        $result = Connection::connect()->prepare("SELECT id FROM gift_cards WHERE card = ? AND amount = ? LIMIT 1");
        $result->bind_param("sd", $this->card, $this->amount);
        $result->execute();
        $result = $result->get_result();
        return $result->num_rows > 0 ? $this->id = $result->fetch_assoc()['id'] : -1;
    }

    public static function empty(): GiftCard
    {
        return new GiftCard(-1, new DateTime(), 0.0, "");
    }

    public function __toString(): string
    {
        return "GiftCard: $this->id, $this->date, $this->amount, $this->card";
    }

    public function __toArray(): array
    {
        return (array)$this;
    }

    public function is_empty(): bool
    {
        return $this->id == -1;
    }
}