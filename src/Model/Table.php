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
        //Init query
        $query = "CREATE TABLE $this->table (";
       
        //Add columns
        foreach ($this->definition as $column => $format) {
            $query .=
                "`".$format['Field']."` "
                . strtoupper($format['Type']) . ' '
                . ($format['Null'] == 'NO' ? 'NOT NULL' : '') . ' '
                . strtoupper($format['Extra']) . ' '
                . ($format['Key'] == 'PRI' ? 'PRIMARY KEY' : '') . ' '
                . ($format['Default'] ? 'DEFAULT ' . $format['Default'] : '')
                . ($format !== end($this->definition) ? ', ' : ');');
        }

        //Execute query
        return (bool) $this->db->query($query);
    }

    /**
     * Alter an existing table to match with the provided definition.
     * 
     * @return bool
     */
    private function alterTable()
    {
        if($this->checkTableDefinition())
            return true;

        //Reset the primary key
        $this->maybeResetPrimaryKey();
        //Init the query
        $query = "ALTER TABLE $this->table ";

        //Modify columns
        foreach ($this->definition as $column) {
            $query .= ($this->columnExists($column['Field']) ? "CHANGE `".$column['Field']."` " : "ADD ") .
                "`".$column['Field']."` "
                . strtoupper($column['Type']) . ' '
                . ($column['Null'] == 'NO' ? 'NOT NULL' : '') . ' '
                . strtoupper($column['Extra']) . ' '
                . ($column['Key'] == 'PRI' ? 'PRIMARY KEY' : '') . ' '
                . ($column['Default'] ? 'DEFAULT ' . $column['Default'] : '')
                . ($column !== end($this->definition) ? ', ' : ';');
        }

        //Execute query
        $this->db->query($query);

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
        return !empty($this->db->query("SHOW TABLES LIKE '$this->table';"));
    }

    /**
     * Check if a column exists in the table.
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function columnExists($name)
    {
        return (bool) $this->db->query("SHOW COLUMNS FROM $this->table WHERE field = '$name';");
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

        //If definition looks good return true
        return true;
    }

    /**
     * Returns the definition for a given Mobbex table name.
     * 
     * @param string $tableName Mobbex table name
     * 
     * @return array
     */
    public static function getTableDefinition($name)
    {
        return include __DIR__ . "/../utils/table-definition/$name.php";
    }

}