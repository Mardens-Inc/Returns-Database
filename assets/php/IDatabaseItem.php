<?php

/**
 * Interface IDatabaseItem
 *
 * @package ReturnsDatabase
 */

namespace ReturnsDatabase;

use Exception;

/**
 * Interface IDatabaseItem
 *
 * Provides methods for interacting with database items.
 */
interface IDatabaseItem
{
    /**
     * Creates an instance of IDatabaseItem from a JSON array
     *
     * @param array $row The JSON array containing the data for the IDatabaseItem
     * @return IDatabaseItem The created instance of IDatabaseItem
     */
    public static function from_json(array $row): IDatabaseItem;

    /**
     * Returns the database item with the given ID.
     *
     * @param int $id The ID of the database item to retrieve.
     * @return IDatabaseItem|null The database item with the given ID if found, or null if not found.
     */
    public static function by_id(int $id): ?IDatabaseItem;

    /**
     * Search for a given query and return an array of results.
     *
     * @param string $query The search query.
     *
     * @return array An array of search results.
     */
    public static function search(string $query): array;

    /**
     * Get all items from the database.
     *
     * @return array
     */
    public static function all(): array;

    /**
     * Saves the current instance to the database.
     *
     * @return void
     */
    public function save(): void;

    /**
     * Deletes the current object from the database.
     *
     * @return void
     * @throws Exception If an error occurs while deleting the object.
     *
     */
    public function delete(): void;

    /**
     * Updates the entity in the database.
     *
     * @return void
     */
    public function update(): void;

    /**
     * Check if the resource exists in the database, returns the id if it does or -1.
     *
     * @return int The id of the resource if it exists, -1 otherwise.
     */
    public function exists(): int;

    /**
     * Get an empty instance of the IDatabaseItem interface.
     *
     * @return IDatabaseItem An empty instance of the IDatabaseItem interface.
     */
    public static function empty(): IDatabaseItem;

    /**
     * Check if the value of a variable is empty.
     *
     * @return bool Returns true if the value is empty; otherwise, false.
     */
    public function is_empty(): bool;

    /**
     * Returns the string representation of the object.
     *
     * @return string The string representation of the object.
     */
    public function __toString(): string;

    /**
     * Converts the current object to an array representation.
     *
     * @return array
     */
    public function __toArray(): array;
}