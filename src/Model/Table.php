<?php

namespace Mobbex\Model;

/**
 * Class Table
 *
 * This class manages the creation of Mobbex tables in a dinamic way to be able to update them flexibly.
 *
 */
class Table
{
    /** Platform DB connection */
    public $db;

    /** Table name with platform prefix */
    public $table;

    /** Array with the table definition */
    public $definition;

    /** The result of query execution */
    public $result;

    /**
     * Constructor
     * 
     * @param string $name Table name.
     * @param array $definition {
     *     @type array {
     *         @type string $Field Column name in sql syntax.
     *         @type string $Type Column type in sql syntax (ex. INT(11)).
     *         @type string $Null If column is nulleable or not (ex. 'NO').
     *         @type string $Key If column is primary key (ex. 'PRI').
     *         @type string $Extra Extra in sql syntax (ex. auto_increment).
     *         @type string $Default Default sql value in sql syntax (ex. current_timestamp()).
     *     }
     * }
     */
    public function __construct($name, $definition = [])
    {
        $this->definition = $definition ?: self::getTableDefinition($name);
        $this->db         = \Mobbex\Platform::$db;
        $this->table      = $this->db->prefix.'mobbex_'.$name;

        //Create the table
        $this->result = $this->tableExists() ? $this->alterTable() : $this->createTable();
    }

    /**
     * Creates a new table in the database with the provided definition.
     * 
     * @return bool
     */
    private function createTable()
    {
        $statements = [];

        foreach ($this->definition as $column)
            $statements[] = $this->buildStatement($column);

        return (bool) $this->db->query(
            sprintf("CREATE TABLE `$this->table` (%s) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;", implode(', ', $statements))
        );
    }

    /**
     * Alter an existing table to match with the provided definition.
     * 
     * @return bool
     */
    private function alterTable()
    {
        $statements = [];

        if ($this->checkTableDefinition())
            return true;

        // Reset the primary key
        $this->maybeResetPrimaryKey();

        // Build query
        foreach ($this->definition as $column) {
            // Try to get column from db to check if its change
            $currentColumn = $this->getColumn($column['Field']);

            // Only add if it has changes on definition
            if ($currentColumn != $column)
                $statements[] = $this->buildStatement($column, $currentColumn ? 'CHANGE' : 'ADD');
        }

        $this->db->query(
            sprintf("ALTER TABLE `$this->table` %s;", implode(', ', $statements))
        );
        
        if(!$this->checkCharset())
            $this->db->query("ALTER TABLE `$this->table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            
        return $this->checkTableDefinition();
    }

    /**
     * Resets the primary key of the table.
     */
    public function maybeResetPrimaryKey()
    {
        //Get the primary key
        $primaryKey = $this->db->query("SHOW keys FROM $this->table WHERE key_name = 'PRIMARY'");

        //If primary key exists
        if(!empty($primaryKey)) {
            //Get column name
            $name = $primaryKey[0]['Column_name'];
            
            //get the column
            $column = $this->db->query("SHOW COLUMNS FROM $this->table LIKE '$name';")[0];

            //Drop the primary key if the definition is diferent
            if(!in_array($column, $this->definition)){
                //Delete the auto_increment
                $this->db->query("ALTER TABLE $this->table MODIFY COLUMN `$name` ". strtoupper($column['Type']) . ";");

                //Drop the primary key
                $this->db->query("ALTER TABLE $this->table DROP PRIMARY KEY;");
            }
        }
    }

    /**
     * Check if a table exists in database.
     * 
     * @return bool
     */
    public function tableExists()
    {
        return (bool) $this->db->query("SHOW TABLES LIKE '$this->table';");
    }

    /**
     * Returns a column if exists in the table or false if not.
     * 
     * @param string $name
     * 
     * @return array|bool
     */
    public function getColumn($name)
    {
        $result = $this->db->query("SHOW COLUMNS FROM $this->table WHERE field = '$name';");
        return isset($result[0]) ? $result[0] : false;
    } 

    /**
     * Check if the table definition is correct.
     * 
     * @return bool 
     */
    public function checkTableDefinition()
    {
        //Get all the columns
        $columns = $this->db->query("SHOW COLUMNS FROM $this->table;");

        //Check column definition
        foreach ($this->definition as $column)
            if(!in_array($column, $columns))
                return false;

        //Drop deprecated columns
        foreach ($columns as $column)
            if (!in_array($column, $this->definition, true))
                $this->db->query("ALTER TABLE $this->table DROP COLUMN " . $column['Field'] . ";");

        //Check that the table has the correct charset
        return $this->checkCharset();
    }

    /**
     * Retrieve the definition for the table given.
     * 
     * @param string $tableName Mobbex table name
     * 
     * @return array
     */
    public static function getTableDefinition($name)
    {
        return include __DIR__ . "/../utils/table-definition/$name.php";
    }

    /**
     * Build a statement query from a column definition.
     * 
     * @param array $column Column definition array.
     * @param null|string $operation Query operation. Leave empty for creation.
     * 
     * @return string 
     */
    public function buildStatement($column, $operation = null)
    {
        return implode(' ', array_filter([
            $operation == 'CHANGE'  ? "CHANGE `$column[Field]`"  : $operation,
            "`$column[Field]`",
            strtoupper($column['Type']),
            strtoupper($column['Extra']),
            $column['Null'] == 'NO' ? 'NOT NULL'                 : null,
            $column['Key'] == 'PRI' ? 'PRIMARY KEY'              : null,
            $column['Default']      ? "DEFAULT $column[Default]" : null
        ]));
    }

    /**
     * Check if the charset is utf8mb4.
     * 
     * @return bool
     */
    public function checkCharset()
    {
        //Get columns data
        $columnData = $this->db->query("SHOW FULL COLUMNS FROM $this->table;");

        //Return false if collation isnt utf8mb4
        foreach ($columnData as $data) {
            if(!empty($data['Collation']) && $data['Collation'] !== 'utf8mb4_general_ci')
                false;
        }

        //If looks good return true
        return true;
    }
}