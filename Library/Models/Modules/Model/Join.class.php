<?php

namespace Models\Modules\Model;

class Join {
    
    /**
     *
     * @var Table 
     */
    private $table;
    
    /**
     *
     * @var Table
     */
    private $mainTable;
    
    /**
     *
     * @var Attribute
     */
    private $attribute;
    
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
    
    
    public function __construct(Attribute $attribute, Table $mainTable) {
        
        
        $this->attribute = $attribute;
        $this->table = new Table($attribute->getClass());
        $this->mainTable = $mainTable;
        
        
        $this->tableAlias = $this->table->getAlias();
        $this->fieldSeed = $this->table->getFieldSeed();
    }
    
    public function toQueryAttributes($fields = Array()) {
        $this->table->getAttributes();
        $query = "";
        $i = 0;
        
        foreach ($this->table->getDataBaseFields() as $field) {
            
            if (sizeof($fields)==0 || in_array($field, $fields)) {
                $query .= ($i > 0 ? ", " : "") . "{$this->tableAlias}.$field AS {$field}_{$this->fieldSeed}";
                $i++;
            }
            
        }
        return $query;
    }
    
    public function getName() {
        return $this->attribute->getName();
    }
    
    public function toQueryJoin($where = Array()) {
        $onClause = "";
        
        if (sizeof($where) > 0) {
            
        } else {
            $onClause = $this->getOnClause();
        }
        
        $table = $this->table->getTableName();
        $query = " LEFT JOIN  {$table} {$this->tableAlias} ON ({$onClause}) ";
        
        return $query;
    }
    
    
    private function getOnClause() {
        $on = "";
        $annotations = $this->attribute->getAnnotations();
        
        if (isset($annotations[1]) && in_array("ForeignKey", $annotations[1])) {
            
            $foreignKey = trim($annotations[2][array_search("ForeignKey", $annotations[1])]);
            $aliasForeignKey = $this->mainTable->getAlias();
            $tableAlias = $this->table->getAlias();
            $on = " {$tableAlias}.id = {$aliasForeignKey}.{$foreignKey} ";
        }
        
        return $on;
    }
    
}