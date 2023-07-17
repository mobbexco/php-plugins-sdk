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
    /** List of warnings in the model */
    public $warning = [];

    /** Platform DB connection */
    public $db;

    /** Table name with platform prefix */
    public $table;

    /** Array with the table definition */
    public $definition;

    /**
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
        $this->definition = empty($definition) ? include __DIR__ . "/../utils/table-definition/$name.php" : $definition;
        $this->db         = \Mobbex\Platform::$db;
        $this->table      = $this->db->prefix.'mobbex_'.$name;

        //Create the table
        $this->init();
    }

    /**
     * Init the Table model.
     */
    private function init()
    {
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
                . (!empty($format['Default']) ? 'DEFAULT ' . $format['Default'] : '')
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
        $this->resetPrimaryKey();
        //Init the query
        $query = "ALTER TABLE $this->table ";

        //Modify columns
        foreach ($this->definition as $format) {
            $query .= ($this->columnExists($format['Field']) ? "CHANGE `".$format['Field']."` " : "ADD ") .
                "`".$format['Field']."` "
                . strtoupper($format['Type']) . ' '
                . ($format['Null'] == 'NO' ? 'NOT NULL' : '') . ' '
                . strtoupper($format['Extra']) . ' '
                . ($format['Key'] == 'PRI' ? 'PRIMARY KEY' : '') . ' '
                . (!empty($format['Default']) ? 'DEFAULT ' . $format['Default'] : '')
                . ($format !== end($this->definition) ? ', ' : ';');
        }

        //Execute query
        $this->db->query($query);

        return $this->checkTableDefinition();
    }

    /**
     * Resets the primary key of the table.
     */
    public function resetPrimaryKey()
    {
        //Query to get the primary key of the table
        $getPrimaryKey = "SELECT COLUMN_NAME
            FROM information_schema.key_column_usage
            WHERE TABLE_NAME = '".$this->table."' AND CONSTRAINT_NAME = (
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE table_name = '$this->table'
                AND constraint_type = 'PRIMARY KEY'
            );";

        //Get the primary key name
        $result = $this->db->query($getPrimaryKey);

        //If primary key exists
        if(!empty($result)) {
            //Get column name
            $name = $result[0]['COLUMN_NAME'];
            //Drop the primary key
            foreach ($this->definition as $column) {
                if($column['Field'] !== $name)
                    continue;
                //Delete the auto_increment
                $this->db->query("ALTER TABLE $this->table MODIFY COLUMN `$name` ".($column['Extra'] == 'auto_increment' ? 'INT(11)' : strtoupper($column['Type'])).";");
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
    public function columnExists($name){
        //Get all the columns
        $columns = $this->db->query("SHOW COLUMNS FROM $this->table;");
        //Return true if the column exits
        foreach ($columns as $column)
            if($column['Field'] === $name)
                return true;
        //Return false if not
        return false;
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

        //Check deprecated columns
        foreach ($columns as $column)
            if(!in_array($column, $this->definition))
                $this->warning[] = "The column " . $column['Field'] . " of the table $this->table didn't exists in the definition";

        //Check column definition
        foreach ($this->definition as $column)
            if(!in_array($column, $columns))
                return false;

        //If definition looks good return true
        return true;
    } 

}