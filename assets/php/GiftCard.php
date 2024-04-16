<?php

namespace ReturnsDatabase;

use DateTime;
use Exception;

class GiftCard
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

    /**
     * Creates a gift card object from a database row
     *
     * @param array $row The database row from which to create the gift card
     *
     * @return GiftCard The created gift card object
     * @throws Exception If the date cannot be parsed
     */
    static function fromJson(array $row): GiftCard
    {
        return new GiftCard($row['id'] ?? -1, new DateTime($row['date']), $row['amount'], $row['card']);
    }

    /**
     * Retrieves a gift card by its ID
     *
     * @param int $id The ID of the gift card to retrieve
     *
     * @return GiftCard|null The retrieved gift card object if found, null otherwise
     */
    public static function byId(int $id): ?GiftCard
    {
        $db = Connection::connect();
        $stmt = $db->prepare('SELECT * FROM gift_cards WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row === false) {
            return null;
        }

        try {
            return self::fromJson((array)$row);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retrieves all gift cards from the database.
     *
     * @return array An array of gift card objects.
     */
    public static function getAll(): array
    {
        $db = Connection::connect();
        $results = $db->query("SELECT * FROM gift_cards");
        $giftCards = [];
        while ($row = $results->fetch_assoc()) {
            try {
                $giftCards[] = self::fromJson($row);
            } catch (Exception) {
                continue;
            }
        }
        return $giftCards;
    }

    /**
     * Inserts a gift card into the database
     *
     * @return bool Returns true if the gift card was successfully inserted, false otherwise
     * @throws Exception Throws an exception if there is an error preparing or executing the statement
     */
    public function insert(): bool
    {
        $exists = $this->checkIfExists();
        if ($exists) {
            $this->id = $exists->id;
            return true;
        }

        $connection = Connection::connect();
        $stmt = $connection->prepare("INSERT INTO `gift_cards` (amount, card) VALUES (?, ?)");

        if ($stmt === false) {
            throw new Exception("Failed to prepare the statement");
        }

        $stmt->bind_param("ds", $this->amount, $this->card);

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute the statement");
        }

        $this->id = $stmt->insert_id;

        return $stmt->affected_rows > 0;
    }

    /**
     * Checks if a gift card with a specific amount and card exists in the database
     *
     * @return false|GiftCard False if the gift card doesn't exist, otherwise the retrieved gift card object
     * @throws Exception
     */
    public function checkIfExists(): false|GiftCard
    {
        $db = Connection::connect();
        $stmt = $db->prepare("SELECT * FROM gift_cards WHERE amount = ? AND card = ?");
        $stmt->bind_param("ds", $this->amount, $this->card);
        $stmt->execute();
        $result = $stmt->get_result();
//        die(json_encode($this));
        return $result->num_rows > 0 ? self::fromJson($result->fetch_assoc()) : false;
    }


}