<?php

namespace Models\Modules\Model;

class Table {
    
    /**
     *
     * @var \ReflectionClass 
     * 
     */
    private $reflection;
    
    /**
     *
     * @var Array() 
     */
    private $annotations;
    
    /**
     *
     * @var Array 
     */
    private $attributes;
    
    /**
     *
     * @var String 
     */
    private $dataBaseFields;
    
    
    /**
     *
     * @var Sring 
     */
    private $tableAlias;
    
    /**
     *
     * @var String 
     */
    private $fieldSeed;
    
    
    /**
     *
     * @var Array
     */
    private $joins;
    
    public function __construct($object) {
        $reflection = new \ReflectionClass($object);
        $this->reflection = $reflection;
        $this->annotations = CommomRegex::extract($reflection->getDocComment());
        
        $this->tableAlias = CommomRegex::getRandonAlias();
        $this->fieldSeed = CommomRegex::getRandonAlias();
    }
    
    public function getDocs() {
        return $this->reflection->getDocComment();
    }
    
    
    public function getTableName() {
        if(isset($this->annotations[1])) {
            if (in_array("Table", $this->annotations[1])) {
                return $this->annotations[2][array_search("Table", $this->annotations[1])];
            }
        }
        $name = explode("\\", $this->reflection->getName());
        return (sizeof($name) > 0 ? $this->getDatabaseName($name[sizeof($name) - 1]) : "");
    }
    
    public function getAnnotations() {
        return $this->annotations;
    }
    
    
    public function getAttributes() {
        if (!sizeof($this->attributes) > 0) {
            $properties = $this->reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($properties as $property) {
                
                if (!$property->isStatic()) {
                    $attribute = new Attribute($property);
                    if($attribute->isTransiente()) {
                        
                        $this->joins[] = new Join($attribute, $this);
                        
                    } else {
                        $this->dataBaseFields[] = $attribute->getDatabaseName();
                        $this->attributes[] = $attribute;
                    }
                }
            }
        }
        return $this->attributes;
    }
    
    public function getDataBaseFields() {
        $this->getAttributes();
        return $this->dataBaseFields;
    }
    
    private function getDatabaseName($a) {
        
        $field ="";
        for ($i = 0; $i< strlen($a); $i++) {
            $b = substr($a, $i, 1);
            
            if (in_array($b, Array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"))) {
                $b = strtolower("_{$b}");
            }
            
            $field .= $b;
        }
        
        return $field;
    }
    
    private function getArrayParams($array = null) {
        $f = Array();
        if (sizeof($array) > 0) {
            foreach ($array as $d) {
                $fa = explode(".", $d);
                
                if (sizeof($fa) > 1) {
                    $f[$fa[0]][] = $fa[1];
                } else {
                    $f["this"][] = $fa[0];
                }
                
            }
        }
        return $f;
    }
    
    public function toQuery($fields = Array(), $where = null, $order = null, $limit = null,  $offset = null) {
        $this->getAttributes();
        
        if (sizeof($fields) > 0) {
            $fields = $this->getArrayParams($fields);
        }
        
        $queryFields = $this->toQueryAttributes((isset($fields["this"]) ? $fields["this"] : Array()));
        $joinTables = "";
        
        if (sizeof($this->joins) > 0) {
            foreach ($this->joins as $join) {
                //$join = new Join($attribute);
                
                $queryFields = $queryFields . (empty($queryFields) ? "" : ", ") . $join->toQueryAttributes((isset($fields[$join->getName()]) ? $fields[$join->getName()] : Array())); 
                $joinTables = " {$joinTables} " . $join->toQueryJoin(null);
            }
        }        
        
        $table = $this->getTableName();
        $query = " SELECT {$queryFields} FROM {$table} {$this->tableAlias} {$joinTables};";
        
        return $query;
    }
    
    public function toQueryAttributes($fields = Array()) {
        $this->getAttributes();
        $query = "";
        $i = 0; 
        foreach ($this->dataBaseFields as $field) {
            
            if (sizeof($fields)==0 || in_array($field, $fields)) {
                $query .= ($i > 0 ? ", " : "") . "{$this->tableAlias}.$field AS {$field}_{$this->fieldSeed}";
                $i++;
            }
            
        }
        return $query;
    }
    
    
    public function getAlias() {
        return $this->tableAlias;
    }
    
    
    public function getFieldSeed() {
        return $this->fieldSeed;
    }
}