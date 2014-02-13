<?php
    /**
     * ChianQueryBuilder
     * 
     * Copyright (c) 2010 BarsMaster
     * e-mail: barsmaster@gmail.com, arthur.borisow@gmail.com
     * 
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     * 
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     * 
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     * 
     * @author BarsMaster
     * @copyright 2010
     * @version 1.1.1
     * @access public
     */
    class libraryDataBaseChainQueryBuilder {
        private $_operation = '';
        private $_fields = array();
        private $_values = array();
        private $_tables = array();
        
        private $_join = '';
        private $_using = '';
        private $_on = array();

        private $_where = array();
        private $_limit = 0;
        private $_offset = 0;

        private $_orderBy = array();
        private $_order = 'ASC';
        private $_groupBy = array();

        // MPWS DATABASE INJECTION
        private $_db_error = "";
        private $_db_errno = 0;
        private $_db_charset = "";
        private $_db_affected_rows = 0;
        private $_db_link_id = 0;
        private $_db_query_id = 0;
        private $_db_selectCount = 0;
        private $_useSanitize = true;
        private $_stopResetTillQuery = false;
        
        // MPWS Patch
        // Init connection in the constructor
        public function __construct($config = false) {
            if (is_array($config))
                $this->connect($config);
        }
        // customer realm detection
        private $_mpwsCustomerRealmUsed = false;

        public function connect($config) {

            //var_dump($config);


            $this->_db_server = $config['DB_HOST'];
            $this->_db_user = $config['DB_USERNAME'];
            $this->_db_pass = $config['DB_PASSWORD'];
            $this->_db_database = $config['DB_NAME'];
            $this->_db_charset = $config['DB_CHARSET'];
            $this->_db_link_id = mysql_connect($this->_db_server, $this->_db_user, $this->_db_pass);

            //var_dump($this->_db_link_id);


            @mysql_query("set names cp1251", $this->_db_link_id);
            if (!$this->_db_link_id) {//open failed
                $this->oops("Could not connect to server: <b>$this->_db_server</b>.");
                }
            if(!@mysql_select_db($this->_db_database, $this->_db_link_id)) {//no database
                $this->oops("Could not open database: <b>$this->_db_database</b>.");
                }
                
            if (! @mysql_set_charset ( $this->_db_charset, $this->_db_link_id )) { // charset error
                $this->oops ( "Could not set charset: <b>$this->_db_charset</b>." );
            }
            // unset the data so it can't be dumped
            $this->_db_server='npws-null';
            $this->_db_user='npws-null';
            $this->_db_pass='npws-null';
            $this->_db_database='npws-null';
        }

        public function escape($string) {
            return $string;
        }

        public function query($sql = '') {
            if (empty($sql))
                $sql = $this->build(true);
            //echo '<br>Query: ' . $sql;
            $this->_useSanitize = true;
            $this->_db_query_id = @mysql_query($sql, $this->_db_link_id);
            if (!$this->_db_query_id) {
                $this->oops("<b>MySQL Query fail:</b> $sql");
                return 0;
            }
            $this->affected_rows = @mysql_affected_rows($this->_db_link_id);
            return $this->_db_query_id;
        }

        public function fetchRow($sql='') {
            if (empty($sql))
                $this->query();
            else
                $this->query($sql);
            $out =  @mysql_fetch_assoc($this->_db_query_id);
            $this->freeResult($this->_db_query_id);//var_dump($out);
            //foreach ($out as &$item)
            //    $item = htmlentities($item, ENT_QUOTES);
            return new mpwsData($out);
        }

        public function getCount($table, $filter = '', $beforeConditionHook = '') {
            if (!empty($filter)) {
                $filter = ' WHERE ' . $filter . ' ';
                if (!empty($beforeConditionHook))
                    $filter = $beforeConditionHook . ' ' . $filter;
            }
            //echo '####SELECT count(*) as `count` FROM ' . $table . $filter;
            $row = $this->fetchRow('SELECT count(*) as `count` FROM ' . $table . $filter);
            if (isset($row['count']))
                return $row['count'];
            return 0;
        }

        public function fetchData($sql='') {
            if (empty($sql))
                $this->query();
            else
                $this->query($sql);
            $out = array();
            while ($row = @mysql_fetch_assoc($this->_db_query_id)){
                //foreach ($row as &$item)
                //    $item = htmlentities($item, ENT_QUOTES);
                $out[] = $row;
            }
            $this->freeResult($this->_db_query_id);//var_dump($out);

            return new mpwsData($out);
        }

        function freeResult($query_id=-1) {
            if ($query_id!=-1) {
                $this->_db_query_id=$query_id;
            }
            if($this->_db_query_id!=0 && !@mysql_free_result($this->_db_query_id)) {
                $this->oops("Result ID: <b>$this->query_id</b> could not be freed.");
            }
        }

        public function oops($msg='') {
            echo $msg;
            echo mysql_error($this->_db_link_id);
            echo mysql_errno($this->_db_link_id);
        }

        public function reset() {
            
            if ($this->_stopResetTillQuery) {
                //echo '<br>reset skipped';
                $this->_stopResetTillQuery = false;
                return $this;
            }
            
            $this->_operation = '';
            $this->_fields = array();
            $this->_values = array();
            $this->_tables = array();

            $this->_join = '';
            $this->_using = '';
            $this->_on = array();

            $this->_where = array();
            $this->_limit = 0;
            $this->_offset = 0;

            $this->_orderBy = array();
            $this->_order = 'ASC';
            $this->_groupBy = array();

            $this->_useSanitize = true;
            $this->_mpwsCustomerRealmUsed = false;
            
            return $this;
        }
        
        public function stopSanitize () {
            $this->_useSanitize = false;
            return $this;
        }
        public function stopReset () {
            //echo '<br>stop reset till first query';
            $this->_stopNextReset = true;
            return $this;
        }
        
        public function getNewID () {
            return mysql_insert_id();
        }

        // END OF MPWS DATABASE INJECTION

        public function orderBy($orderBy) {
            $args = $this->_getArgs(func_get_args());
            $this->_orderBy = $args;
            return $this;
        }

        public function order($order) {
            $order = strtoupper($order);
            if (in_array($order, array('ASC', 'DESC'))) {
                $this->_order = $order;
            }
            return $this;
        }

        public function groupBy($groupBy) {
            $args = $this->_getArgs(func_get_args());
            $this->_groupBy = $args;
            return $this;
        }

        public function offset($offset = 0) {
            $offset = (int)abs($offset);
            if ($offset) {
                $this->_offset = $offset;
            }
            return $this;
        }
        
        public function join($table) {
            $this->_join = 'JOIN ' . $table;
            return $this;
        }
        
        public function leftJoin($table) {
            $this->_join = 'LEFT JOIN ' . $table;
            return $this;
        }
        
        public function rightJoin($table) {
            $this->_join = 'RIGHT JOIN ' . $table;
            return $this;
        }
        
        public function using($field) {
            $this->_using = $field;
            return $this;
        }
        
        public function on($c1, $operand, $c2) {
            return $this->_addWhereOn($c1, $operand, $c2, '', 'on');
        }
        
        public function andOn($c1, $operand, $c2) {
            return $this->_addWhereOn($c1, $operand, $c2, 'AND', 'on');
        }
        
        public function orOn($c1, $operand, $c2) {
            return $this->_addWhereOn($c1, $operand, $c2, 'AND', 'on');
        }

        public function limit($limit = 0) {
            $limit = (int)abs($limit);
            $this->_limit = $limit;
            return $this;
        }

        public function select($fields) {
            
            // mpws patch
            if ($this->_operation == 'SELECT')
                return $this;
            
            $this->_setOperation('select');
            //$this->_operation = 'SELECT';
            
            $args = $this->_getArgs(func_get_args());
            foreach ($args as $arg) {
                $this->addField($arg);
            }
            return $this;
        }

        public function from($tables) {
            if ($this->_operation != 'SELECT') {
                throw new Exception('Only SELECT operators.');
            }
            $this->_tables = $this->_getArgs(func_get_args());
            return $this;
        }

        public function where($cond1, $operand, $cond2) {
            return $this->_addWhereOn($cond1, $operand, $cond2, '', 'where');
        }

        public function andWhere($cond1, $operand, $cond2) {
            return $this->_addWhereOn($cond1, $operand, $cond2, 'AND', 'where');
        }

        public function orWhere($cond1, $operand, $cond2) {
            return $this->_addWhereOn($cond1, $operand, $cond2, 'OR', 'where');
        }

        private function _addWhereOn($cond1, $operand, $cond2, $type, $property) {

            /* start mpws patch: customer realm detection */
            $this->_mpwsCustomerRealmUsed = ($cond1 === 'CustomerID');
            /* end mpws patch: customer realm detection */

            $operand = strtoupper($operand);

            if (!in_array($operand, array('=', '>', '<', '<>', '!=', '<=', '>=', 'LIKE', 'IN'))) {
                throw new Exception('Unsupported operand:' . $operand);
            }
            $this->{'_' . $property}[] = array(
                                    'cond1' => $cond1,
                                    'operand' => $operand,
                                    'cond2' => $cond2,
                                    'type' => $type
                                );
            return $this;
        }

        public function addField($field) {
            if (!in_array($field, $this->_fields)) {
                $this->_fields[] = $field;
            }
            return $this;
        }

        protected function _sanitizeValue($val, $search = false) {
            if (!$this->_useSanitize)
                return $val;
            if (!is_numeric($val)) {
                //$val = addcslashes(mysql_real_escape_string($val), "%_");
                $val = mysql_real_escape_string($val);
                $val = '\'' .  $val . '\'';
            }
            //echo $val;
            return $val;
        }

        public function insertInto($table) {
            $this->_setOperation('insert');
            $this->_tables[] = $table;
            return $this;
        }

        public function fields($fields) {
            if (!in_array($this->_operation, array('INSERT', 'UPDATE'))) {
                throw new Exception('Only INSERT and Update operations.');
            }
            $args = $this->_getArgs(func_get_args());
            $this->_fields = $args;
            return $this;
        }

        public function values($values) {
            if (!in_array($this->_operation, array('INSERT', 'UPDATE'))) {
                throw new Exception('Only INSERT and Update operations.');
            }
            $args = $this->_getArgs(func_get_args());
            if (count($args) != count($this->_fields)) {
                throw new Exception('Number of values has to be equal to the number of fields.');
            }
            if ($this->_operation == 'INSERT') {
                $this->_values[] = $args;
            } elseif ($this->_operation == 'UPDATE') {
                $this->_values = $args;
            }
            return $this;
        }

        public function deleteFrom($table) {
            $this->_setOperation('delete');
            $args = $this->_getArgs(func_get_args());
            $this->_tables = $args;
            return $this;
        }

        public function update($table) {
            $this->_setOperation('update');
            $this->_tables = array($table);
            return $this;
        }

        public function set($field) {
            $args = func_get_args();
            if (count($args) == 2) {
                $args = array($args[0] => $args[1]);
            } else {
                $args = $this->_getArgs(func_get_args());

            }
            foreach ($args as $field => $val) {
                if (!in_array($field, $this->_fields)) {
                    $this->_fields[] = $field;
                    $this->_values[] = $val;
                }
            }

            return $this;
        }

        private function _setOperation($operation) {
            if ($this->_operation) {
                throw new Exception('Can\'t modify the operator.');
            } elseif (!in_array($operation, array('select', 'insert', 'delete', 'update'))) {
                throw new Exception('Unsupported operator:' . strtoupper($operation));
            } else {
                $operation = strtoupper($operation);
                $this->_operation = $operation;
            }
        }

        private function _getArgs($args) {
            $argsCnt = count($args);
            if (!$argsCnt) {
                return array();
            }

            if ($argsCnt == 1) {
                if (!is_array($args[0])) {
                    return array($args[0]);
                }
                return $args[0];
            } else {
                $return = array();

                foreach ($args as $arg) {
                    $return[] = $arg;
                }

                return $return;
            }
        }

        public function build($reset = false) {

            /* start mpws patch: customer realm detection */
            if (!$this->_mpwsCustomerRealmUsed && !glIsToolbox()) {
                $ctx = contextMPWS::instance();
                $customerID = $ctx->contextCustomer->getCustomerID();
                if ($customerID)
                    $this->andWhere('CustomerID', '=', $customerID);
                else
                    throw new Exception("Error Processing Request: Customer ID is not specified");
                    
            }
            /* end mpws patch: customer realm detection */

            $statement = array();
            $this->_buildOperator($statement);
            $op = '_build' . $this->_operation;
            $this->$op($statement);
            
            $this->_buildJoin($statement);

            $this->_buildWhereOn($statement, 'where');

            $this->_buildGroupBy($statement);

            $this->_buildOrderBy($statement);

            $this->_buildLimit($statement);

            // mpws reset all values
            if ($reset)
                $this->reset();
            // mpws reset all values

            return implode(' ', $statement);
        }
        
        private function _buildJoin(&$statement) {
            if (!$this->_join) {
                return;
            }
            $statement[] = $this->_join;
            if ($this->_using) {
                $statement[] = 'USING(' . $this->_using . ')';
            }
            $this->_buildWhereOn($statement, 'on');
        }
        private function _buildUpdate(&$statement) {
            $statement[] = implode(', ', $this->_tables);
            $statement[] = 'SET';
            $set = array();
            foreach($this->_fields as $k => $f) {
                $set[] = $f . ' = ' . $this->_sanitizeValue($this->_values[$k]);
            }
            $statement[] = implode(', ', $set);
        }
        private function _buildDELETE(&$statement) {
            $statement[] = 'FROM ' . implode(', ', $this->_tables);
        }
        private function _buildSELECT(&$statement) {
            $statement[] = implode(', ', $this->_fields);
            $statement[] = 'FROM ' . implode(', ', $this->_tables);
        }

        private function _buildINSERT(&$statement) {
            $statement[] = 'INTO';
            $statement[] = implode(', ', $this->_tables);
            $this->_buildINSERTFields($statement);
            $statement[] = 'VALUES';
            $this->_buildINSERTValues($statement);
        }

        private function _buildINSERTFields(&$statement) {
            $statement[] = '(' . implode(', ', $this->_fields) . ')';
        }

        private function _buildINSERTValues(&$statement) {
            $values = array();
            foreach ($this->_values as $val) {
                foreach ($val as & $v) {
                    $v = $this->_sanitizeValue($v);
                }
                $values[] = '(' . implode(', ', $val) . ')';
            }
            $statement[] = implode(', ', $values);
        }

        private function _buildOperator(&$statement) {
            $statement[] = $this->_operation;
        }

        private function _buildWhereOn(&$statement, $type) {
            if (!in_array($this->_operation, array('UPDATE', 'DELETE', 'SELECT'))) {
                return;
            }
            if (count($this->{'_' . strtolower($type)})) {
                $statement[] = strtoupper($type);
                foreach ($this->{'_' . strtolower($type)} as $where) {
                    $tmp = array($where['type'], $where['cond1'], $where['operand']);
                    if ($where['operand'] != 'IN') {
                        if ($type == 'where') {
                            $tmp[] = $this->_sanitizeValue($where['cond2'], $where['operand'] == 'LIKE');
                        } else {
                            $tmp[] = $where['cond2'];
                        }
                    } else {
                        $ins = array();
                        if (!is_array($where['cond2'])) {
                            $ins = array($where['cond2']);
                        } else {
                            foreach($where['cond2'] as $c2) {
                                $ins[] = $this->_sanitizeValue($c2, false);
                            }
                        }
                        $tmp[2] = $tmp[2] . '(' . implode(', ', $ins) . ')';
                    }
                    $statement[] = implode(' ', $tmp);
                }
            }
        }

        private function _buildGroupBy(&$statement) {
            if ($this->_operation != 'SELECT') {
                return;
            }
            if (count($this->_groupBy)) {
                $statement[] = 'GROUP BY';
                $gbs = array();
                foreach ($this->_groupBy as $gb) {
                    $gbs[] = $gb;
                }
                $statement[] = implode(', ', $gbs);
            }
        }

        private function _buildOrderBy(&$statement) {
            if ($this->_operation != 'SELECT') {
                return;
            }
            if (count($this->_orderBy)) {
                $statement[] = 'ORDER BY';
                $obs = array();
                foreach ($this->_orderBy as $ob) {
                    $obs[] = $ob;
                }
                $statement[] = implode(', ', $obs);
                $statement[] = $this->_order;
            }
        }

        private function _buildLimit(&$statement) {
            if ($this->_offset > 0 && $this->_limit > 0) {
                $statement[] = 'LIMIT ' . $this->_offset . ', ' . $this->_limit;
            } elseif ($this->_offset > 0) {
                $statement[] = 'OFFSET ' . $this->_offset;
            } elseif ($this->_limit > 0) {
                $statement[] = 'LIMIT ' . $this->_limit;
            }
        }
        
        // utils
        
        public function getFields($table) {
            return $this->fetchData('SHOW COLUMNS FROM ' . $table);
        }

        public function getEnumValues($table, $field) {
            
            if (empty($field))
                return array();
            
            $data = $this->fetchRow('SHOW COLUMNS FROM ' . $table . ' WHERE Field LIKE \'' . $field . '\'');

            if (empty($data['Type']))
                return array();

            $matches = null;
            $returnValue = preg_match_all('/\'(.*?)\'/', $data['Type'], $matches);
            if ($returnValue == 0)
                return array();
            
            return $matches[1];
        }
        
        public static function parseEnum ($dtField) {
            $matches = null;
            $returnValue = preg_match_all('/\'(.*?)\'/', $dtField, $matches);
            if ($returnValue == 0)
                return array();
            
            return $matches[1];
        }

        // <<<<<< MPWS PATCH:

        public function mpwsGetData ($dataConfig) {

            $sourceMain = $dataConfig['source'];
            $fields = $dataConfig['fields'];

            // $customFields = [];

            $this->reset();

            $this->select($fields);

            // if (!$dataConfig['alias'])
            //     $this->from(sprintf("%s as `%s`", $sourceMain, $aliasMain));
            // else
            $this->from($sourceMain);

            // join additional data
            // if (!empty($dataConfig['additional'])) {
            // }

            // conditional select
            $_conditionasAdded = 0;
            if (!empty($dataConfig['condition'])) {
                //echo 'adding condition';
                $_cnd = explode(' ', $dataConfig['condition'], 3);
                //var_dump($_cnd);
                if ($_conditionasAdded == 0)
                    $this->where(trim($_cnd[0], ' \'`"'), trim($_cnd[1]), trim($_cnd[2], ' \'"') . ' ');
                else
                    $this->andWhere(trim($_cnd[0], ' \'`"'), trim($_cnd[1]), trim($_cnd[2], ' \'"') . ' ');
                $_conditionasAdded++;
            }

            if (!empty($dataConfig['offset']))
                $this->offset($dataConfig['offset']);

            if (!empty($dataConfig['limit']))
                $this->limit($dataConfig['limit']);

            $data = $this->fetchData();

            if (isset($dataConfig['output']))
                return $data->to($dataConfig['output']);

            return $data;
        }

        public function mpwsFetchDataByConfig ($config) {

            $fieldsToSelectFromDB = $config['fields'];

            // prepend ID column
            array_unshift($fieldsToSelectFromDB, 'ID');

            // just to avoid mysql error: XXXX in field list is ambiguous
            foreach ($fieldsToSelectFromDB as $key => $value) {
                if (!strstr($value, '.'))
                    $fieldsToSelectFromDB[$key] = sprintf("%s.%s", $config['source'], $value);
            }

            $this->reset();

            // join another data
            if (isset($config['valuesOverride'])) {

                $joinCounter = 0;

                foreach ($config['valuesOverride'] as $customFieldName => $joinCondition) {

                    if (!empty($joinCondition['source']) && 
                        !empty($joinCondition['linker']) &&
                        !empty($joinCondition['valueToDisplay'])) {

                        $linker = $joinCondition['linker'];

                        // check wheter linker is valid
                        if (empty($linker['link']) ||
                            empty($linker['with']) ||
                            empty($linker['type']))
                            continue; // <--- linker is not valid

                        $fieldOfSource = sprintf("%s.%s", $joinCondition['source'], $linker['link']);
                        $fieldOfDest = sprintf("%s.%s", $config['source'], $linker['with']);

                        // if ($joinCounter === 0) {
                            $this
                                ->join($joinCondition['source'])
                                ->on($fieldOfSource, $linker['type'], $fieldOfDest);
                            // $joinCounter++;
                        // } else
                            // $this->andOn($fieldOfSource, $linker['type'], $fieldOfDest);

                        // add into field list to select
                        $fieldsToSelectFromDB[] = sprintf("%s.%s as `%s`",
                            $joinCondition['source'], $joinCondition['valueToDisplay'], $customFieldName);

                        // remove custom filed name from regular fields
                        $filedToRemoveAsCustom = sprintf("%s.%s", $config['source'], $customFieldName);
                        if(($key = array_search($filedToRemoveAsCustom, $fieldsToSelectFromDB)) !== false)
                            unset($fieldsToSelectFromDB[$key]);
                    }
                }
            }

            $this->select(implode(', ', $fieldsToSelectFromDB))->from($config['source']);

            $data = $this->fetchData();
            // var_dump($data);

            if (isset($config['output']))
                return $data->to($config['output']);
            
            return $data;
        }

        // <<<<<< MPWS PATCH
    }
    
?>