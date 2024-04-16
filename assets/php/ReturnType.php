<?php

namespace ReturnsDatabase;

enum ReturnType: int
{
    /**
     * When there is no receipt for a return under $10.
     */
    case NoReceiptUnder10 = 0;

    /**
     * When there is no receipt and store credit is given (Mardens Retail Credit)
     */
    case NoReceiptMRC = 1;

    /**
     * When there is a receipt and either cash, check or chargeback on the debit card was used.
     */
    case WithReceiptCCD = 2;

    /**
     * When there is a receipt and store credit is given (Mardens Retail Credit)
     */
    case WithReceiptMRC = 3;

    /**
     * Parses the given number and returns the corresponding ReturnType.
     *
     * @param int $num The number to parse.
     *
     * @return ReturnType|null The corresponding ReturnType or null if no match is found.
     */
    static function parse(int $num): ?ReturnType
    {
        return match ($num) {
            0 => ReturnType::NoReceiptUnder10,
            1 => ReturnType::NoReceiptMRC,
            2 => ReturnType::WithReceiptCCD,
            3 => ReturnType::WithReceiptMRC,
            default => null,
        };
    }
}