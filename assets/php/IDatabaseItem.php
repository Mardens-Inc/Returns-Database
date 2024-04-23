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
     * Performs a search based on the given query with optional limit, offset, sort column and sort order.
     *
     * @param string $query The search query.
     * @param int $limit The maximum number of results to return.
     * @param int $offset The offset from where to start returning results.
     * @param string $sort_column The column to sort the results by.
     * @param bool $ascending Whether to sort the results in ascending order.
     * @return array The search results.
     */
    public static function search(string $query, int $limit, int $offset, string $sort_column, bool $ascending): array;

    /**
     * Get a range of items from the database and return them as an array.
     *
     * @param int $limit The maximum number of items to retrieve.
     * @param int $offset The number of items to skip before starting the range.
     * @param string $sort_column The column to sort the items by.
     * @param bool $ascending Whether to sort the items in ascending order.
     *
     * @return array An array of items within the specified range.
     */
    public static function range(int $limit, int $offset, string $sort_column, bool $ascending): array;

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
    public function jsonSerialize(): array;

    /**
     * Reloads the current object from the database and returns it as an instance of IDatabaseItem.
     *
     * @return IDatabaseItem The reloaded object from the database.
     */
    public function reload_from_database(): IDatabaseItem;

    /**
     * Count the number of records in the database.
     *
     * @return int The number of records in the database.
     */
    public static function count(): int;
}