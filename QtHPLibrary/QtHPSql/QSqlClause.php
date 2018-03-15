<?php

class QSqlClause extends QAbstractObject {
    
    const Asc = 'ASC',
          Desc = 'DESC',
            
          OpOr = 'OR',
          OpAnd = 'AND';
    
    private $_where = '',
            $_order = array(),
            $_group = array(),
            $_having = '',
            $_limit = null,
            $_offset = null;
    
    public function __construct($clause = null) {
        if(is_array($clause)){
            foreach(array('where', 'orderBy', 'groupBy', 'having') as $c){
                if(isset($clause['add' . ucfirst($c)])){
                    $this->{$c}($clause[$c]);
                }
            }
            if(isset($clause['limit'])){
                $this->limit($clause['limit']);
            }
            if(isset($clause['offset'])){
                $this->offset($clause['offset']);
            }
        } else if($clause instanceof QSqlClause){
            $this->_group = $clause->_group;
            $this->_having = $clause->_having;
            $this->_limit = $clause->_limit;
            $this->_offset = $clause->_offset;
            $this->_order = $clause->_order;
            $this->_where = $clause->_where;
        } else if($clause !== null){
            throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::__construct(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
    
    public function addGroupBy($name){
        if($name instanceof QSqlClause){
            $this->_group += $name->_group;
        } else if(is_array($name)){
            foreach($name as $v){
                if(!is_string($v)){
                    throw new QSqlClauseGroupByException('Group by clause must contain string values only, aborting');
                }
                $this->_groups[$v] = true;
            }
        } else if(is_string($name)){
            $this->_groups[$name] = true;
        } else {
            throw new QSqlClauseSignatureException('Group by clause must contain string values only');
        }
        return $this;
    }
    
    public function addHaving($cond, $op = null){
        if ($cond instanceof QSqlClause){
            if($this->_having == null && $op == null){
                $this->_having = '(' . $cond->_having . ')';
            } else if($this->_having != null){
                if($op == null)
                    $op = self::OpAnd;
                $this->_having .= ' ' . $op . ' (' . $cond->_having . ')';
            } else {
                throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addHaving(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
        } else if(is_array($cond)){
            if($op == null && $this->_having !== null){
                $this->_having = '('  . implode($op, $cond) . ')';
            } else if($this->_having != null){
                if($op == null)
                    $op = self::OpAnd;
                $this->_having .= ' ' . $op . ' ('  . implode(self::OpAnd, $cond) . ')';
            } else {
                throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addHaving(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
        } else if(is_string($cond)){
            if($this->_having == null && $op == null){
                $this->_having = $cond;
            } else if($this->_having != null){
                if($op == null)
                    $op = self::OpAnd;
                $this->_having .= ' ' . $op . ' ' . $cond;
            } else {
                throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addHaving(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
        } else {
            throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addHaving(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return $this;
    }
    
    public function addOrderBy($name, $op = null){
        if(is_string($name)){
            if($op == null){
                $op = self::Asc;
            } else if(!is_string($op)){
                throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addOrderBy(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
            $this->_order[] = $name . ' ' . $op;
        }
        return $this;
    }
    
    public function addWhere($cond, $op = null){
        if ($cond instanceof QSqlClause){
            if($this->_where == null && $op == null){
                $this->_where = $cond->where();
            } else if($this->_where != null) {
                if($op == null){
                    $op = self::OpAnd;
                }
                $this->_where .= ' ' . $op . ' (' . $cond->where() . ')';
            } else {
                throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addWhere(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
        } else if(is_array($cond)){
            if($op == null && $this->_where == null){
                $this->_where = '('  . implode($op, $cond) . ')';
            } else if($this->_where != null) {
                if($op == null)
                    $op = self::OpAnd;
                $this->_where .= ' ' . $op . ' ('  . implode(self::OpAnd, $cond) . ')';
            } else {
                throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addWhere(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
        } else if(is_string($cond)){
            if($this->_where == null && $op == null){
                $this->_where = $cond;
            } else if($this->_where != null){
                if($op === null)
                    $op = self::OpAnd;
                $this->_where .= ' ' . $op . ' ' . $cond;
            } else {
                throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addWhere(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
        } else {
            throw new QSqlClauseSignatureException('Call to undefined function QSqlClause::addWhere(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return $this;
    }
    
    public function setLimit($limit, $offset = null){
        if(!is_scalar($limit) || !preg_match('/[\d+]/', $limit)){
            throw new QSqlClauseSignatureException('Limit must be a positive integer');
        }
        if($offset)
            $this->setOffset($offset);
        $this->_limit = $limit;
        return $this;
    }
    
    public function setOffset($offset){
        if(!is_scalar($offset) || !preg_match('/[\d+]/', $offset)){
            throw new QSqlClauseSignatureException('Offset must be a positive integer');
        }
        $this->_offset = $offset;
        return $this;
    }
    
    public function groupBy(){
        return count($this->_group) ? ' GROUP BY ' . implode(', ', $this->_group) : '';
    }
    
    public function having(){
        return isset($this->_having{0}) ? ' HAVING ' . $this->_having : '';
    }
    
    public function orderBy(){
        return count($this->_order) ? ' ORDER BY ' . implode(', ', $this->_order) : '';
    }
    
    public function where(){
        return isset($this->_where{0}) ? ' WHERE ' . $this->_where : '';
    }
    
    public function limit(){
        return $this->_limit !== null ? ' LIMIT ' . $this->_limit : '';
    }
    
    public function offset(){
        return $this->_offset !== null ? ' OFFSET ' . $this->_offset : '';
    }
    
    public function toString(){
        return $this->where() . $this->groupBy() . $this->having() . $this->orderBy() . $this->limit() . $this->offset();
    }
}

class QSqlClauseException extends QAbstractObjectException {}
class QSqlClauseSignatureException extends QSqlClauseException implements QSignatureException {}

class QSqlClauseGroupByException extends QSqlClauseException {}

?>