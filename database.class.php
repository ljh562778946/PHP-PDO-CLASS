<?php

/**
 * Database class for MySQL PDO
 * Provides security and quick functionality to your project
 * @Copyright Adam Rollinson <adamleerollinson@gmail.com>
 * @Repo https://github.com/AdamRollinson/PHP-PDO-CLASS
 */


class Database {

    private $connection, $host, $user, $pass, $db, $port;
    private $dsn, $options;
    private $error;
    private $query, $results, $count, $wherearray = array();
    private $operators, $order = array(), $limit = array();

    /**
     * @param $host
     * @param $user
     * @param $pass
     * @param $db
     * @param int $port
     */

    public function __construct($host, $user, $pass, $db, $port = 3306) {

        $this->host = $host; $this->user = $user; $this->pass = $pass; $this->db = $db; $this->port = $port;

        $this->dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db}";

        $this->options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        );

        try {
            $this->connection = new PDO($this->dsn, $this->user, $this->pass, $this->options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
        }

        $this->operators = array('=', '>', '<', '>=', '<=', '!=');
    }

    /**
     * @param $table
     * @param array $options
     * @return bool|string
     */

    public function _insert($table, $options = array()) {

        $keys = array();
        $values = array();

        foreach ($options as $key => $value) {
            $keys[] = "`".$key."`";
            $values[] = $value;
        }

        $fields = implode(" = ?, ", $keys);
        $fields = $fields . " = ?";

        $i = 1;

        try {
            $this->query = $this->connection->prepare("insert into `{$table}` set ".$fields);
        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        foreach($values as $value) {

            $type = null;

            if(is_null($type)) {
                switch(true) {
                    case is_int($value):
                        $type = PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = PDO::PARAM_NULL;
                        break;
                    default:
                        $type = PDO::PARAM_STR;
                }
            }

            $this->query->bindValue($i, $value, $type);
            $i++;
        }

        try {
           return $this->query->execute();
        } catch(PDOException $e) {
            return $e->getMessage();
        }

    }

    /**
     * @param $table
     * @param array $options
     * @return bool|string
     */

    public function _update($table, $options = array()) {

        $keys = array();
        $values = array();
        $wValues = array();
        $query = array();

        foreach ($options as $key => $value) {
            $keys[] = "`".$key."`";
            $values[] = $value;
        }

        $fields = implode(" = ?, ", $keys);
        $fields = $fields . " = ?";

        foreach($this->wherearray as $array) {

            if($array['query'] != null) {
                $query[] = $array['query'];

                if($array['value'] != null) {
                    $wValues[] = $array['value'];
                }

            }

        }

        $i = implode(" ? and ", $query);

        $where = $i . " ?";

        $i = 1;


            try {
                $this->query = $this->connection->prepare("update `{$table}` set " . $fields . " where {$where}");
            } catch(PDOException $e) {
                echo $e->getMessage();
            }

            foreach ($values as $value) {

                $type = null;

                if (is_null($type)) {
                    switch (true) {
                        case is_int($value):
                            $type = PDO::PARAM_INT;
                            break;
                        case is_bool($value):
                            $type = PDO::PARAM_BOOL;
                            break;
                        case is_null($value):
                            $type = PDO::PARAM_NULL;
                            break;
                        default:
                            $type = PDO::PARAM_STR;
                    }
                }
                $this->query->bindValue($i, $value, $type);
                $i++;
            }

        foreach($wValues as $value) {

            switch($value) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }

            $this->query->bindValue($i, $value, $type);
            $i++;
        }

            try {
                return $this->query->execute();
                $this->wherearray = null;
            } catch (PDOException $e) {
                return $e->getMessage();
            }

    }

    /**
     * @param $table
     * @return string
     */

    public function _delete($table) {
        return $this->action("delete", $table);
    }

    /**
     * @param $table
     * @return string
     */

    public function _select($table) {
        return $this->action("select *", $table);
    }

    /**
     * @return mixed
     */

    public function _rows() {
        return $this->count();
    }

    /**
     * @return mixed
     */

    public function _results() {
        return $this->results;
    }

    /**
     * @param $action
     * @param $table
     * @return string
     */

    private function action($action, $table) {

        $values = array();
        $query = array();

        if($this->wherearray != null) {

            foreach ($this->wherearray as $array) {

                if ($array['query'] != null) {
                    $query[] = $array['query'];

                    if ($array['value'] != null) {
                        $values[] = $array['value'];
                    }

                }

            }

            $i = implode(" ? and ", $query);

            $where = "where ". $i . " ?";

        }

            try {

                if($this->order != null && $this->limit == null) {
                    $extra = $this->order;
                }

                if($this->limit != null && $this->order == null) {
                    $extra = $this->limit;
                }

                if($this->limit != null && $this->order != null) {
                    $extra = $this->order . " " .$this->limit;
                }

                $this->query = $this->connection->prepare("{$action} from `{$table}` {$where} {$extra}");
            } catch(PDOException $e) {
                echo $e->getMessage();
            }


                $i2 = 1;

                foreach($values as $value) {

                    switch($value) {
                        case is_int($value):
                            $type = PDO::PARAM_INT;
                            break;
                        case is_bool($value):
                            $type = PDO::PARAM_BOOL;
                            break;
                        case is_null($value):
                            $type = PDO::PARAM_NULL;
                            break;
                        default:
                            $type = PDO::PARAM_STR;
                    }

                    $this->query->bindValue($i2, $value, $type);
                    $i2++;
                }

                try {

                    if($this->query->execute()) {

                        $this->wherearray = null;
                        $this->order = null;
                        $this->limit = null;

                        $this->results = $this->query->fetchAll(PDO::FETCH_ASSOC);
                        $this->count = $this->query->rowCount();
                    }

                } catch(PDOException $e) {
                    return $e->getMessage();
                }


    }

    /**
     * @param $field
     * @param $operator
     * @param $value
     * @return array
     */

    public function _where($field, $operator, $value) {

        if(in_array($operator, $this->operators)) {

            $this->wherearray[] = array(
                'query' => "`".$field ."` ".$operator,
                'value' => $value

            );

            return $this->wherearray;

        }

    }

    /**
     * @param $limit
     * @return array|string
     */
    
    public function _limit($limit) {

        $this->limit = "limit {$limit}";

        return $this->limit;
    }

    /**
     * @param $field
     * @param bool $desc
     * @return array|string
     */

    public function _order($field, $desc = false) {

        if($desc == true) {
            $desc = "desc";
        }

        $this->order = "order by `{$field}` {$desc}";

        return $this->order;

    }


    /**
     * @return string
     */

    public function _last_insert_id() {
        return $this->connection->lastInsertId();
    }

    /**
     * @return bool
     */

    public function _cancelTransaction() {
        return $this->connection->rollBack();
    }

    /**
     * @return bool
     */

    public function _endTransaction() {
        return $this->connection->commit();
    }

    /**
     * @return bool
     */

    public function _beginTransaction() {
        return $this->connection->beginTransaction();
    }

} 