<?php

namespace app\utils;

use support\Db;

/**
 * 表结构查询工具类
 * 提供多种方式查询数据库表结构信息
 */
class TableStructureUtil
{
    /**
     * 获取表的所有字段信息
     * @param string $tableName 表名
     * @return array 字段信息数组
     */
    public static function getTableFields(string $tableName): array
    {
        $result = Db::query("DESCRIBE `$tableName`");
        $fields = [];
        foreach ($result as $row) {
            $fields[] = $row['Field'];
        }
        return $fields;
    }

    /**
     * 获取表的详细结构信息
     * @param string $tableName 表名（不含前缀）
     * @return array 包含完整字段信息的数组
     */
    public static function getTableStructure(string $tableName): array
    {
        // 获取数据库配置中的表前缀
        $prefix = config('database.connections.mysql.prefix', '');
        $fullTableName = $prefix . $tableName;
        
        $fieldTypes = Db::select("
        SELECT 
            COLUMN_NAME as field_name,
            DATA_TYPE as data_type,
            IS_NULLABLE as is_nullable,
            CHARACTER_MAXIMUM_LENGTH as max_length,
            COLUMN_DEFAULT as default_value,
            COLUMN_KEY as key_type,
            EXTRA as extra_info,
            NUMERIC_PRECISION,
            NUMERIC_SCALE,
            COLUMN_COMMENT as comment
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = ?
        ORDER BY ORDINAL_POSITION", [$fullTableName]);
        
        return $fieldTypes;
    }

    /**
     * 获取数据库中所有表名
     * @return array 表名数组
     */
    public static function getAllTables(): array
    {
        $prefix = config('database.connections.mysql.prefix', '');
        $tables = Db::select("SHOW TABLES");
        
        $tableNames = [];
        foreach ($tables as $table) {
            $tableName = reset($table);
            // 如果配置了表前缀，则过滤掉非当前项目的表
            if (!empty($prefix) && strpos($tableName, $prefix) === 0) {
                $tableNames[] = substr($tableName, strlen($prefix)); // 移除前缀
            } elseif (empty($prefix)) {
                $tableNames[] = $tableName;
            }
        }
        
        return $tableNames;
    }

    /**
     * 检查表是否存在
     * @param string $tableName 表名（不含前缀）
     * @return bool 是否存在
     */
    public static function tableExists(string $tableName): bool
    {
        $prefix = config('database.connections.mysql.prefix', '');
        $fullTableName = $prefix . $tableName;
        
        $result = Db::select("SHOW TABLES LIKE ?", [$fullTableName]);
        return count($result) > 0;
    }

    /**
     * 获取表的索引信息
     * @param string $tableName 表名（不含前缀）
     * @return array 索引信息
     */
    public static function getTableIndexes(string $tableName): array
    {
        $prefix = config('database.connections.mysql.prefix', '');
        $fullTableName = $prefix . $tableName;
        
        $indexes = Db::select("SHOW INDEX FROM `$fullTableName`");
        return $indexes;
    }

    /**
     * 获取表的基本信息（如注释等）
     * @param string $tableName 表名（不含前缀）
     * @return array 表信息
     */
    public static function getTableInfo(string $tableName): array
    {
        $prefix = config('database.connections.mysql.prefix', '');
        $fullTableName = $prefix . $tableName;
        
        $tableInfo = Db::select("
            SELECT 
                TABLE_NAME,
                ENGINE,
                TABLE_COLLATION,
                TABLE_COMMENT,
                TABLE_ROWS,
                DATA_LENGTH,
                CREATE_TIME,
                UPDATE_TIME
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_NAME = ?
        ", [$fullTableName]);
        
        return $tableInfo;
    }
}