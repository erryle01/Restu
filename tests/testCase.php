<?php

declare(strict_types=1);

class TestCase
{
    public const SKIP = '__SKIP__';

    public static function assertTrue(
        bool $condition,
        string $message = 'Expected condition to be true.'
    ): void {

        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    public static function assertFalse(
        bool $condition,
        string $message = 'Expected condition to be false.'
    ): void {

        if ($condition) {
            throw new RuntimeException($message);
        }
    }

    public static function assertEquals(
        mixed $expected,
        mixed $actual,
        string $message = ''
    ): void {

        if ($expected !== $actual) {

            if ($message === '') {
                $message =
                    'Expected ' .
                    var_export($expected, true) .
                    ' but received ' .
                    var_export($actual, true);
            }

            throw new RuntimeException($message);
        }
    }

    public static function assertNotNull(
        mixed $value,
        string $message = 'Expected value not to be null.'
    ): void {

        if ($value === null) {
            throw new RuntimeException($message);
        }
    }

    public static function assertContains(
        mixed $needle,
        array $haystack,
        string $message = ''
    ): void {

        if (!in_array($needle, $haystack, true)) {

            if ($message === '') {
                $message =
                    'Expected array to contain ' .
                    var_export($needle, true);
            }

            throw new RuntimeException($message);
        }
    }

    public static function assertHasTable(
        PDO $db,
        string $table
    ): void {

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ");

        $stmt->execute([
            $table
        ]);

        $exists =
            (int) $stmt->fetchColumn();

        self::assertEquals(
            1,
            $exists,
            "Table '{$table}' does not exist."
        );
    }

    public static function assertHasColumn(
        PDO $db,
        string $table,
        string $column
    ): void {

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
            AND table_name = ?
            AND column_name = ?
        ");

        $stmt->execute([
            $table,
            $column
        ]);

        $exists =
            (int) $stmt->fetchColumn();

        self::assertEquals(
            1,
            $exists,
            "Column '{$table}.{$column}' does not exist."
        );
    }
}