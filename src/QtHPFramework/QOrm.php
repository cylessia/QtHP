<?php
/**
 * @todo : Do something about update and keys !
 * @todo : Analyze relations while deleting
 */
class QOrm extends QAbstractObject {
    
    const
        Fields = 'a',
        Keys = 'b',
        Joins = 'c',
        Name = 'd',
        Alias = 'e',
        Inherits = 'f',
        Relations = 'g',
        Type = 'h',
        Autoload = 'i',
        
        HasMany = 'j',
        BelongsTo = 'k',
        HasOne = 'l',
        HasManyToMany = 'm',
        
        Model = 'n',
        Callbacks = 'o';
    
    /**
     * Do HasMany and HasManyToMany insert and update
     * Insert a movie and all its relative actors to show the team
     * When it works correctely, insert at least 5 movies with all people
     * After, deal with series, then books 
     */
    
    protected $_dirtyProperties = array(),
            $_properties = array(),
            $_db,
            $_junction = null;
    
    protected static $_definition = array();
    
    public function __construct(QSqlDatabase $db = null){
        $this->_db = $db ?: QDispatcher::current()->di()->getShared('db');
        $this->init();
    }
    
    protected function init(){}


    public function __set($name, $value){
        $model = $this->type();
        $ns = substr($model, 0, strrpos($model, '\\')+1);
        do {
            if(isset($model::$_definition[self::Fields][$name])){
                $this->_dirtyProperties[$name] = true;
                method_exists($this, 'set' . $name) ? $this->{'set' . $name}($value) : $this->_properties[$name] = $value;
                return;
            } else if(isset($model::$_definition[self::Keys][$name])){
                $this->_dirtyProperties[$name] = true;
                method_exists($this, 'set' . $name) ? $this->{'set' . $name}($value) : $this->_properties[$name] = $value;
                return;
            } else if(isset($model::$_definition[self::Relations][$name])) {
                switch($model::$_definition[self::Relations][$name][self::Type]){
                    case self::BelongsTo:
                    case self::HasOne:
                        if(!$value instanceof $model::$_definition[self::Relations][$name][self::Model]){
                            throw new QOrmSetException($model . '::' . $name . ' must be instance of ' . $model::$_definition[self::Relations][$name][self::Model]);
                        }
                        $this->_properties[$name] = $value;
                        $this->_dirtyProperties[$name] = true;
                        return;
                        break;
                    case self::HasMany:
                    case self::HasManyToMany:
                        if($value instanceof QList){
                            $this->_properties[$name] = $value->toArray();
                            $this->_dirtyProperties[$name] = true;
                        } else if(is_array($value)){
                            $this->_properties[$name] = $value;
                            $this->_dirtyProperties[$name] = true;
                        } else {
                            throw new QOrmSetException($model . '::' . $name . ' must be instance of array or QList');
                        }
                        return;
                        break;
                    default:
                        throw new QOrmRelationTypeException('Relation ' . $model . '::' . $name . ' has an unknown relation (Found ' . $model::$_definition[self::Relations][$name][self::Type] . ')');
                }
            }
            if(isset($model::$_definition[self::Inherits])){
                reset($model::$_definition[self::Inherits]);
                $model = $ns.key($model::$_definition[self::Inherits]);
            } else {
                $model = null;
            }
        } while($model);
        throw new QOrmSetException('Trying to set undefined property ' . $this->type() . '::' . $name);
    }
    
    
    // What if is a key or a field of its parent
    public function __get($name){
        $model = $this->type();
        $ns = substr($model, 0, strrpos($model, '\\')+1);
        do {
            if(isset($this->_properties[$name])){
                return $this->_properties[$name];
            } else if(isset($model::$_definition[self::Fields][$name]) || isset($model::$_definition[self::Keys][$name])){
                return null;
            } else if(isset($model::$_definition[self::Relations][$name])){
                if(count($keys = array_intersect_key($this->_properties, array_merge($model::$_definition[self::Fields], $model::$_definition[self::Keys])))){
                    switch($model::$_definition[self::Relations][$name][self::Type]){
                        case self::BelongsTo:
                        case self::HasOne:
                            $c = $ns.$model::$_definition[self::Relations][$name][self::Model];
                            $clause = new QSqlClause;
                            $vars = array();
                            foreach($model::$_definition[self::Relations][$name][self::Keys] as $ref => $loc){
                                $clause->addWhere($ref . ' = :' . $ref);
                                $vars[$ref] = $keys[$loc];
                            }
                            return $this->_properties[$name] = $c::select($clause, $vars, $this->_db);
                            break;
                        case self::HasMany:
                            $c = $ns.$model::$_definition[self::Relations][$name][self::Model];
                            $clause = new QSqlClause;
                            $vars = array();
                            foreach($model::$_definition[self::Relations][$name][self::Keys] as $ref => $loc){
                                $clause->addWhere($ref . ' = :' . $ref);
                                $vars[$ref] = $keys[$loc];
                            }
                            return $this->_properties[$name] = $c::find($clause, $vars, $this->_db);
                            break;
                        case self::HasManyToMany:
                            return $this->_properties[$name] = $this->_findManyToMany($model::$_definition[self::Relations][$name]);
                            break;
                    }
                } else {
                    throw new QOrmGetException('Trying to get property of unitialized ' . $this->type() . '::' . $name);
                }
            }
            if(isset($model::$_definition[self::Inherits])){
                reset($model::$_definition[self::Inherits]);
                $model = $ns.key($model::$_definition[self::Inherits]);
            } else {
                $model = null;
            }
        }while($model);
        throw new QOrmGetException('Trying to get undefined property ' . $this->type() . '::' . $name);
    }
    
    public function __call($name, $arguments){
        if(substr($name, 0, 3) != 'get'){
            throw new QOrmSignatureException('Call to undefined function ' . $this->type() . '::' . $name . '(' . implode(', ', array_map('qGetType', $arguments)) . ')');
        }
        $model = $this->type();
        $name = lcfirst(substr($name, 3));
        $ns = substr($model, 0, strrpos($model, '\\')+1);
        do {
            if(isset($this->_properties[$name])){
                return $this->_properties[$name];
            } else if(isset($model::$_definition[self::Fields][$name]) || isset($model::$_definition[self::Keys][$name])){
                return null;
            } else if(isset($model::$_definition[self::Relations][$name])){
                if(count($keys = array_intersect_key($this->_properties, array_merge($model::$_definition[self::Fields], $model::$_definition[self::Keys])))){
                    switch($model::$_definition[self::Relations][$name][self::Type]){
                        case self::BelongsTo:
                        case self::HasOne:
                            $c = $ns.$model::$_definition[self::Relations][$name][self::Model];
                            $clause = new QSqlClause;
                            $vars = array();
                            foreach($model::$_definition[self::Relations][$name][self::Keys] as $ref => $loc){
                                $clause->addWhere($ref . ' = :' . $ref);
                                $vars[$ref] = $keys[$loc];
                            }
                            return $this->_properties[$name] = call_user_func_array(array($c, 'select'), $arguments);
                            break;
                        case self::HasMany:
                            $c = $ns.$model::$_definition[self::Relations][$name][self::Model];
                            $clause = new QSqlClause;
                            $vars = array();
                            foreach($model::$_definition[self::Relations][$name][self::Keys] as $ref => $loc){
                                $clause->addWhere($ref . ' = :' . $ref);
                                $vars[$ref] = $keys[$loc];
                            }
                            return $this->_properties[$name] = call_user_func_array(array($c, 'find'), $arguments);
                            break;
                        case self::HasManyToMany:
                            array_unshift($arguments, $model::$_definition[self::Relations][$name]);
                            return $this->_properties[$name] = call_user_func_array(array($this, '_findManyToMany'), $arguments);
                            break;
                    }
                } else {
                    throw new QOrmGetException('Trying to get property of unitialized ' . $this->type() . '::' . $name);
                }
            }
            if(isset($model::$_definition[self::Inherits])){
                reset($model::$_definition[self::Inherits]);
                $model = $ns.key($model::$_definition[self::Inherits]);
            } else {
                $model = null;
            }
        }while($model);
        throw new QOrmGetException('Call to undefined function ' . $this->type() . '::' . $name . '(' . implode(', ', array_map('qGetType', $arguments)) . ')');
    }
    
    public function fill($res){
        $this->_properties = array();
        static::_fill($res, $this, $this->type(), true);
        //var_dump($this);
    }
    
    private static function _fill($res, QOrm $obj, $modelName){
        if(!$res){
            // Set all to null
            foreach($obj->_properties as $prop => $__){
                $obj->_properties[$prop] = null;
            }
        } else {
            $ns = substr($modelName, 0, strrpos($modelName, '\\')+1);
            
            // Fill current object using current model's fields...
            $callbacks = (isset($modelName::$_definition[self::Callbacks])) ? array_flip($modelName::$_definition[self::Callbacks]) : array();
            foreach($modelName::$_definition[self::Fields] as $field => $value){
                $obj->_properties[$field] = isset($callbacks[$field]) ? $obj->{'_set' . ucfirst($field)}($res->$field) : $res->$field;
            }
            // ...and keys
            foreach($modelName::$_definition[self::Keys] as $field => $value){
                $obj->_properties[$field] = $res->$field;
            }
            // Inherits table ?
            if(isset($modelName::$_definition[self::Inherits])){
                foreach(reset($modelName::$_definition[self::Inherits]) as $foreign => $local){
                    $res->$foreign = $obj->_properties[$local];
                }
                self::_fill($res, $obj, $ns.key($modelName::$_definition[self::Inherits]));
            }
            // Now relations (1-1 || n-1) !
            if(isset($modelName::$_definition[self::Relations])){
                foreach($modelName::$_definition[self::Relations] as $name => $def){
                    if((($def[self::Type] == self::HasOne || $def[self::Type] == self::BelongsTo) && ($obj->_junction == $def[self::Model] || !$obj->_junction)) && (!isset($def[self::Autoload]) || $def[self::Autoload])){
                        // What are the keys (and are they set) ?
                        foreach($def[self::Keys] as $foreign => $local){
                            if($obj->_properties[$local] === null){
                                break; // Go to next def
                            }
                            $res->$foreign = $obj->_properties[$local];
                            $c = $ns.$def[self::Model];
                            $obj->_properties[$name] = new $c($obj->_db);
                            $obj->_properties[$name]->fill($res);
                        }
                    }
                }
            }
        }
    }
    
    
    public static function select($clause = null, $values = null, $db = null){
        /**
         * Signature Calls
         * QOrm::select(QSqlDatabase);
         * QOrm::select(mixed);
         * QOrm::select(mixed, QSqlDatabase);
         * QOrm::select(QSqlClause, mixed, null);
         * QOrm::select(QSqlClause, QSqlDatabase);
         * QOrm::select(QSqlClause, mixed, QSqlDatabase);
         */
        if($clause instanceof QSqlDatabase && $values === null && $db === null){
            $clause = self::_buildClauses();
        } else if(is_scalar($clause) && $values === null && $db === null){
            if(count(static::$_definition[self::Keys]) == 1){
                reset(static::$_definition[self::Keys]);
                $values = array(key(static::$_definition[self::Keys]) => $clause);
                $clause = self::_buildClauses();
            } else {
                throw new QOrmSelectException('Missing some primary keys to select a record of ' . substr($this->type(), 0, strrpos($this->type(), '\\')+1));
            }
        } else if(is_array($clause) && $values === null && $db === null){
            $values = $clause;
            $clause = self::_buildClauses();
        } else if($clause instanceof QSqlClause && $values instanceof QSqlDatabase){
            $db = $values;
            $values = null;
        } else if($clause !== null && $values instanceof QSqlDatabase){
            $db = $values;
            if(is_array($clause)){
                $values = $clause;
            } else if(count(static::$_definition[self::Keys]) == 1){
                reset(static::$_definition[self::Keys]);
                $values = array(key(static::$_definition[self::Keys]) => $values);
                $clause = self::_buildClauses();
            } else {
                throw new QOrmSelectException('Missing some primary keys to select a record of ' . substr($this->type(), 0, strrpos($this->type(), '\\')+1));
            }
        } else if(!($clause instanceof QSqlClause && $values !== null && $db !== null)){
            throw new QOrmSignatureException('Call to undefined function QOrm::select(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $res = static::find($clause, $values, $db);
        return $res->valid() ? $res->current() : null;
    }
    
    private static function _buildClauses(){
        $c = new QSqlClause;
        foreach(static::$_definition[self::Keys] as $k => $__){
            $c->addWhere($k . ' = :' . $k);
        }
        return $c;
    }
    
    public static function find($clause = null, $values = null, $db = null){
        /**
         * Signature Calls
         * QOrm::find();
         * QOrm::find(QSqlDatabase);
         * QOrm::find(QSqlClause);
         * QOrm::find(QSqlClause, QSqlDatabase);
         * QOrm::find(QSqlClause, mixed, null);
         * QOrm::find(QSqlClause, mixed, QSqlDatabase);
         */
        if($clause == null && $values == null && $db == null){
            $db = QDispatcher::current()->di()->getShared('db');
            $values = array();
        } else if($clause instanceof QSqlDatabase && $values === null && $db === null){
            $db = $clause;
            $clause = null;
            $values = array();
        } else if($clause instanceof QSqlClause && $values == null && $db == null){
            $db = QDispatcher::current()->di()->getShared('db');
            $values = array();
        } else if($clause instanceof QSqlClause && $values instanceof QSqlDatabase && $db == null){
            $db = $values;
            $values = array();
        } else if($clause instanceof QSqlClause && is_array($values)){
            if(!$db)
                $db = QDispatcher::current()->di()->getShared('db');
        } else {
            throw new QOrmSignatureException('Call to undefined function QOrm::find(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $sql = self::_sqlQuery(get_called_class()) . ($clause ? $clause->toString() : '');
        
        // Then prepare the query (with the clauses), bind placeholders, execute it and return the QResultSet
        //echo $sql;
        $query = $db->prepare($sql);
        if(count($values)){
            $binds = array_merge(static::$_definition[self::Fields], static::$_definition[self::Keys]);//$bindings = array_merge(static::$_definition[self::Fields], static::$_definition[self::PrimaryKeys])
            foreach($values as $ph => $val){
                //echo '$query->bind(' . (isset($binds[$ph]) ? $binds[$ph] : QSqlQuery::BindString) . ', ' . $ph . ', ' . $val . ')';
                $query->bind(isset($binds[$ph]) ? $binds[$ph] : QSqlQuery::BindString, $ph, $val);
            }
        }
        return new QResultSet($query->exec(), new static($db));
    }
    
    private function _findManyToMany($def, QSqlClause $clause = null){
        // $def Got te appropriate model and the junction table
        $ns = substr($this->type(), 0, strrpos($this->type(), '\\')+1);
        $sql = self::_sqlQuery($ns.$def[self::Model], $def) . ($clause ? $clause->toString() : '');
        //echo $sql;
        // Prepare the query
        $query = $this->_db->prepare($sql);
        //echo '$query = $this->_db->prepare(' . $sql . ');<br />';
        foreach($def[self::Keys][0] as $thisKey){
            //echo '$query->bind(' . static::$_definition[self::Keys][$thisKey] . ', ' . $thisKey . ', ' . $this->_properties[$thisKey] . ');<br />';
            $query->bind(static::$_definition[self::Keys][$thisKey], $thisKey, $this->_properties[$thisKey]);
        }
        $c = $ns.$def[self::Joins];
        $c = new $c($this->_db);
        $c->_junction = $def[self::Model];
        return new QResultSet($query->exec(), $c);
        
        // Add the where clause
    }
    
    protected static function _sqlQuery($model, $relation = null){
        // Main table
        $ns = substr($model, 0, strrpos($model, '\\')+1);
        $aliases = array(($mainAlias = isset($model::$_definition[self::Alias]) ? $model::$_definition[self::Alias] : $model::$_definition[self::Name]) => 0);
        $fields = implode(', ', array_map(function($v)use($mainAlias){return $mainAlias . '.' . $v . ' "' . $v . '"';}, array_keys(array_merge($model::$_definition[self::Fields], $model::$_definition[self::Keys]))));
        $table = $model::$_definition[self::Name] . ($mainAlias == $model::$_definition[self::Name] ? '' : ' ' . $mainAlias);
        $joins = '';
        
        // Does it have inherits table to find also ?
        if(isset($model::$_definition[self::Inherits])){
            foreach($model::$_definition[self::Inherits] as $foreignModel => $keys){
                $foreignModel = $ns . $foreignModel;
                $ons = array();
                $alias = isset($foreignModel::$_definition[self::Alias]) ? $foreignModel::$_definition[self::Alias] : $foreignModel::$_definition[self::Name];
                // Check if it is already in use
                if(isset($aliases[$alias])){
                        $alias .= (++$aliases[$alias]);
                } else {
                    $aliases[$alias] = 0;
                }
                
                $fields .= ', ' . implode(', ', array_map(function($v)use($alias){return $alias . '.' . $v . ' "' . $v . '"';}, array_keys($foreignModel::$_definition[self::Fields])));
                $joins .= ' JOIN ' . $foreignModel::$_definition[self::Name] . ($alias == $foreignModel::$_definition[self::Name] ? '' : ' ' . $alias);
                foreach($keys as $foreignKey => $localKey){
                    $ons[] = $alias . '.' . $foreignKey . ' = ' . $mainAlias . '.' . $localKey;
                }
                $joins .= ' ON ' . implode(' AND ', $ons);
            }
        }
        
        if(isset($model::$_definition[self::Relations])){
            foreach($model::$_definition[self::Relations] as $definition){
                if($definition[self::Type] == self::HasOne || $definition[self::Type] == self::BelongsTo && (!isset($definition[self::Autoload]) && $definition[self::Autoload])){
                    $foreignModel = $ns . $definition[self::Model];
                    $ons = array();
                    $alias = isset($foreignModel::$_definition[self::Alias]) ? $foreignModel::$_definition[self::Alias] : $foreignModel::$_definition[self::Name];
                    // Check if it is already in use
                    if(isset($aliases[$alias])){
                            $alias .= (++$aliases[$alias]);
                    } else {
                        $aliases[$alias] = 0;
                    }
                    $fields .= ', ' . implode(', ', array_map(function($v)use($alias){return $alias . '.' . $v . ' "' . $v . '"';}, array_keys($foreignModel::$_definition[self::Fields])));
                    $joins .= ($definition[self::Type] == self::HasOne ? ' LEFT ' : '') . ' JOIN ' . $foreignModel::$_definition[self::Name] . ($alias == $foreignModel::$_definition[self::Name] ? '' : ' ' . $alias);
                    foreach($definition[self::Keys] as $foreignKey => $localKey){
                        $ons[] = $alias . '.' . $foreignKey . ' = ' . $mainAlias . '.' . $localKey;
                    }
                    $joins .= ' ON ' . implode(' AND ', $ons);
                }
            }
        }
        if($relation){
            // Add the fields and the joins
            $junction = $ns.$relation[self::Joins];
            $def = $junction::$_definition;
            $alias = isset($def[self::Alias]) ? $def[self::Alias] : $def[self::Name];
            // Check if it is already in use
            if(isset($aliases[$alias])){
                    $alias .= (++$aliases[$alias]);
            } else {
                $aliases[$alias] = 0;
            }
            $fields .= ', ' . implode(', ', array_map(function($v)use($alias){return $alias . '.' . $v . ' "' . $v . '"';}, array_keys(array_merge($def[self::Fields], $def[self::Keys]))));
            $ons = array();
            foreach($relation[self::Keys][1] as $junctionKey => $modelKey){
                $ons[] = $alias . '.' . $junctionKey . ' = ' . $mainAlias . '.' . $modelKey;
            }
            foreach($relation[self::Keys][0] as $junctionKey => $modelKey){
                $ons[] = $alias . '.' . $junctionKey . ' = :' . $modelKey;
            }
            $joins .= ' JOIN ' . $def[self::Name] . ($alias == $def[self::Name] ? '' : ' ' . $alias) . ' ON ' . implode(' AND ', $ons);
        }
        $sql = 'SELECT ' . $fields . ' FROM ' . $table . $joins;
        //echo $sql;
        return $sql;
    }
    
    public function save(){
        self::_save($this->type(), $this);
    }
    
    public function record(){
        self::_record($this->type(), $this);
    }
    
    private static function _save($modelName, $model){
        try {
            self::_update($modelName, $model);
        } catch(QOrmUpdateException $e){
            self::_insert($modelName, $model);
        }
    }
    
    private static function _record($modelName, $model){
        try {
            self::_insert($modelName, $model);
        } catch(QOrmInsertException $e){
            self::_update($modelName, $model);
        }
    }
    
    public function insert(){
        self::_insert($this->type(), $this);
    }
    
    private static function _insert($modelName, QOrm $model){
        //var_dump($modelName, $model);
        // First, save all 1-n relations
        // (they don't need $this->pks but $this needs them)
        
        $ns = substr($modelName, 0, strrpos($modelName, '\\')+1);
        
        if(isset($modelName::$_definition[self::Relations])){
            foreach($modelName::$_definition[self::Relations] as $prop => $def){
                if(($def[self::Type] == self::HasOne || $def[self::Type] == self::BelongsTo) && isset($model->_dirtyProperties[$prop])){
                    self::_save($def[self::Model], $model->_properties[$prop]);
                }
            }
        }
        
        if(isset($modelName::$_definition[self::Inherits])){
            reset($modelName::$_definition[self::Inherits]);
            self::_insert($ns.key($modelName::$_definition[self::Inherits]), $model);
            // Now, set the keys
            foreach(current($modelName::$_definition[self::Inherits]) as $foreign => $local){
                $model->_properties[$local] = $model->_properties[$foreign];
                $model->_dirtyProperties[$local] = true;
            }
        }
        // Now insert the current obj
        // Is there anything to insert ?
        if(count($dirty = array_intersect_key(array_merge($modelName::$_definition[self::Fields], $modelName::$_definition[self::Keys]), $model->_dirtyProperties))){
            $fields = '(' . implode(', ', array_keys($dirty)) . ') VALUES (' . implode(', ', array_map(function($v){return ':' . $v;}, array_keys($dirty))) . ')';
            $returning = count($returningArray = array_diff_key(array_merge($modelName::$_definition[self::Fields], $modelName::$_definition[self::Keys]), $dirty))
                ? ' RETURNING ' . implode(', ', array_keys($returningArray))
                : '';
        } else {
            $fields = ' DEFAULT VALUES';
            $returning = count($returningArray = array_merge($modelName::$_definition[self::Fields], $modelName::$_definition[self::Keys]))
                ? ' RETURNING ' . implode(', ', array_keys($returningArray))
                : '';
        }
        $sql = 'INSERT INTO ' . $modelName::$_definition[self::Name] . $fields . $returning;
        //echo $sql . '<br />';
        // Prepare, bind, execute !
        $query = $model->_db->prepare($sql);
        $callbacks = (isset($modelName::$_definition[self::Callbacks])) ? array_flip($modelName::$_definition[self::Callbacks]) : array();
        foreach($dirty as $prop => $type){
            //echo '<br />$query->bind(' . $type . ', ' . $prop . ', ' . (isset($callbacks[$prop]) ? $model->{'_get' . ucfirst($prop)}() : $model->_properties[$prop]) . ');';
            $query->bind($type, $prop, isset($callbacks[$prop]) ? $model->{'_get' . ucfirst($prop)}() : $model->_properties[$prop]);
        }
        if(!($query->exec()->numRows())){
            throw new QOrmInsertException('Nothing to insert');
        }
        
        // Remove dirty properties !
        $model->_dirtyProperties = array_diff_key($model->_dirtyProperties, $dirty);
        
        if(($res = $query->fetch())){
            foreach($res as $k => $v){
                $model->_properties[$k] = $v;
            }
        }
        
        // Save all other relationships (this::id is needed)
        if(isset($modelName::$_definition[self::Relations])){
            foreach($modelName::$_definition[self::Relations] as $prop => $def){
                if(isset($model->_dirtyProperties[$prop]) && ($def[self::Type] == self::HasMany || $def[self::Type] == self::HasManyToMany)){
                    foreach($model->_properties[$prop] as $junction){
                        foreach($def[self::Keys][0] as $ref => $loc){
                            $junction->_properties[$ref] = $model->_properties[$loc];
                            $junction->_dirtyProperties[$ref] = true;
                        }
                        self::_save($ns.$def[self::Joins], $junction);
                    }
                }
            }
        }
        return $query;
        
    }
    
    public function update(){
        self::_update($this->type(), $this);
    }
    
    private static function _update($modelName, QOrm $model){
        //var_dump($modelName, $model);
        if(!count($model->_dirtyProperties)){
            throw new QOrmUpdateException('Nothing to update');
        }
        // To update, the keys must be set
        if(count(array_intersect_key($model->_properties, $modelName::$_definition[self::Keys])) !== count($modelName::$_definition[self::Keys])){
            throw new QOrmUpdateException('Primary key missing');
        }
        
        $ns = substr($modelName, 0, strrpos($modelName, '\\')+1);
        
        // Save all HasOne and BelongsTo relationships ($this needs foreign id)
        if(isset($modelName::$_definition[self::Relations])){
            foreach($modelName::$_definition[self::Relations] as $prop => $def){
                if(($def[self::Type] == self::HasOne || $def[self::Type] == self::BelongsTo) && isset($model->_dirtyProperties[$prop])){
                    reset($def[self::Model]);
                    self::_save($ns.key($def[self::Model]), $model->_properties[$prop]);
                }
            }
        }
        
        // Get the current model dirty properties !
        if(count($dirty = array_intersect_key($modelName::$_definition[self::Fields], $model->_dirtyProperties))){
            // Now, create the update function using the dirty properties !
            $sql = 'UPDATE ' . $modelName::$_definition[self::Name]  . ' SET '
                    . implode(', ', array_map(function($v){return $v . ' = :' . $v;}, array_keys($dirty)))
                    . ' WHERE ' . implode(' AND ', array_map(function($v){return $v . ' = :' . $v;}, array_keys($modelName::$_definition[self::Keys])));
            //echo $sql . '<br />';
            $query = $model->_db->prepare($sql);
            // Bind dirty fields
            $callbacks = (isset($modelName::$_definition[self::Callbacks])) ? array_flip($modelName::$_definition[self::Callbacks]) : array();
            foreach($dirty as $prop => $type){
                $query->bind($type, $prop, (isset($model->_properties[$prop]) ? (isset($callbacks[$prop]) ? $model->{'_get'.ucfirst($prop)}() : $model->_properties[$prop]) : null));
                //echo '$query->bind(' . $type . ', ' . $prop . ', ' . (isset($model->_properties[$prop]) ? (isset($callbacks[$prop]) ? $model->{'_get'.ucfirst($prop)}() : $model->_properties[$prop]) : null) . ')' . '<br />';
            }
            
            // Bind all keys primary
            foreach($modelName::$_definition[self::Keys] as $prop => $type){
                $query->bind($type, $prop, $model->_properties[$prop]);
                //echo '$query->bind(' . $type . ', ' . $prop . ', ' . (isset($model->_properties[$prop]) ? $model->_properties[$prop] : null) . ')' . '<br />';
            }
            $query->exec();
            if(!($query->exec()->numRows())){
                throw new QOrmUpdateException('Nothing to insert');
            }

            // Remove dirty properties !
            $model->_dirtyProperties = array_diff_key($model->_dirtyProperties, array_merge($dirty, $modelName::$_definition[self::Keys]));
        } else {
            $query = null;
        }
        
        // Update the parent
        if(isset($modelName::$_definition[self::Inherits])){
            reset($modelName::$_definition[self::Inherits]);
            self::_update($ns.key($modelName::$_definition[self::Inherits]), $model);
            
        }
        
        // Save all other relationships (this::id is needed)
        if(isset($modelName::$_definition[self::Relations])){
            foreach($modelName::$_definition[self::Relations] as $prop => $def){
                if(isset($model->_dirtyProperties[$prop]) && ($def[self::Type] == self::HasMany || $def[self::Type] == self::HasManyToMany)){
                    foreach($model->_properties[$prop] as $junction){
                        foreach($def[self::Keys][0] as $ref => $loc){
                            $junction->_properties[$ref] = $model->_properties[$loc];
                            $junction->_dirtyProperties[$ref] = true;
                        }
                        self::_save($ns.$def[self::Joins], $junction);
                    }
                }
            }
        }
        return $query;
    }
    
    public function delete(){
        self::_delete(get_called_class(), $this);
    }
    
    private static function _delete($modelName, QOrm $model){
        $ns = substr($modelName, 0, strrpos($modelName, '\\')+1);
        
        // To delete, the keys must be set
        if(count(array_intersect_key($model->_properties, $modelName::$_definition[self::Keys])) !== count($modelName::$_definition[self::Keys])){
            throw new QOrmDeleteException('Primary key missing');
        }
        $sql = 'DELETE FROM ' . $modelName::$_definition[self::Name] . ' WHERE '
               . implode(' AND ', array_map(function($v){return $v . ' = :' . $v;}, array_keys($modelName::$_definition[self::Keys])));
        //echo $sql;
        $query = $model->_db->prepare($sql);
        foreach($modelName::$_definition[self::Keys] as $ph => $type){
            //echo '$query->bind(' . $type . ', ' . $ph . ', ' . $model->_properties[$ph] . ')<br />';
            $query->bind($type, $ph, $model->_properties[$ph]);
            unset($model->_properties[$ph]);
            if(isset($model->_dirtyProperties[$ph])) {
                unset($model->_dirtyProperties[$ph]);
            }
            $model->_dirtyProperties = array_fill_keys(array_keys($model->_properties), true);
        }
        $query->exec();
        
        // Delete its parent !
        if(isset($modelName::$_definition[self::Inherits])){
            reset($modelName::$_definition[self::Inherits]);
            foreach(current($modelName::$_definition[self::Inherits]) as $foreign => $local){
                $model->_properties[$local] = $model->_properties[$foreign];
            }
            self::_delete($ns.key($modelName::$_definition[self::Inherits]), $model);
        }
        return $query;
    }
}

class QOrmException extends QAbstractObjectException {}
class QOrmSignatureException extends QOrmException implements QSignatureException{}
class QOrmUpdateException extends QOrmException {}
class QOrmInsertException extends QOrmException {}
class QOrmSetException extends QOrmException {}
class QOrmGetException extends QOrmException {}
class QOrmRelationTypeException extends QOrmException {}
?>