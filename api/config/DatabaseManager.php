<?php

require_once __DIR__ . '/../database.php';

class DatabaseManager
{
    /**
     * جميع المشاريع كما هى فى databases.php
     */
    private static ?array $projects = null;

    /**
     * فهرس الجداول
     * مثال:
     *
     * [
     *     'movies' => [Database, Database],
     *     'series' => [Database]
     * ]
     */
    private static ?array $tableMap = null;

    /**
     * تحميل المشاريع وبناء الفهرس مرة واحدة
     */
    private static function load(): void
    {
        if (self::$projects !== null) {
            return;
        }

        self::$projects = require __DIR__ . '/../databases.php';

        self::$tableMap = [];

        foreach (self::$projects as $project) {

            $db = new Database(
                $project['url'],
                $project['key']
            );

            foreach ($project['tables'] as $table) {

                if (!isset(self::$tableMap[$table])) {
                    self::$tableMap[$table] = [];
                }

                self::$tableMap[$table][] = $db;
            }
        }
    }

    /**
     * جميع المشاريع
     */
    public static function all(): array
    {
        self::load();

        return self::$projects;
    }

    /**
     * قواعد البيانات الخاصة بجدول معين
     */
    public static function databasesForTable(string $table): array
    {
        self::load();

        return self::$tableMap[$table] ?? [];
    }

    /**
     * أول قاعدة (للإضافة والتعديل والحذف)
     */
    public static function firstDatabase(string $table): ?Database
    {
        $list = self::databasesForTable($table);

        return $list[0] ?? null;
    }

    /**
     * هل الجدول موجود؟
     */
    public static function tableExists(string $table): bool
    {
        self::load();

        return isset(self::$tableMap[$table]);
    }
}