<?php

namespace Models\Modules\Model;

class Where {
    
    /**
     *
     * @var Array
     */
    private $where = Array();
    
    private $keyWords = Array("AND", "OR", "IN", "NIN", "LIKE", "GTE", "LTE", "GT", "LT", "BETWEEN", "DIF", "NLIKE", "EQ");
    private $keyMap = Array("AND" => "AND", "OR" => "OR", "IN" => "IN", "NIN" => " NOT IN ", "LIKE", "GTE", ">=", "LTE" => "<=", "GT" => ">", "LT" => "<", "BETWEEN" => "BETWEEN", "DIF" => "!=", "NLIKE" => "NOT LIKE", "EQ" => "=");
    private $keyValues = Array("NULL", "NOT NULL");
    private $valuesMap = Array("NULL" => "IS NULL", "IS NOT NULL");
    
    public function __construct($where) {
        
        if (is_string($where)) {
            $this->where[] = $where;
        } else {
            $this->where = $where;
        }
    }
    
    
    public function toWhereString() {
        
        return $this->arrayToString($this->where);
        
    }
    
    
    private function arrayToString($where) {
        $sWhere = "";
        
        foreach ($where as $key => $value) {
            
            if (in_array(strtoupper($key), $this->keyWords)) {
                $sWhere .= $this->specialClause($key, $value);
            } else {
                $sWhere .= $this->toSimpleClause($key, "=", $value);
            }
            
            echo $sWhere . "<br>";
        }
        
        return $sWhere;
    }
    
    
    private function specialClause($key, $value) {
        $key = strtoupper($key);
        $sWhere = "";
        
        $operator = $this->keyMap[$key];
            
        if (in_array($key, Array("AND", "OR"))) {

            if (is_string($value)) {
                $sWhere .= " {$operator} {$value}";
            } else if (is_array($value)) {

                foreach ($value as $field=>$v) {

                    if (is_array($v) && is_string($field)) {
                        $sWhere .= specialClause($field, $v); // tem outro operador dentro do anterior, repiro a recursão
                    } else if (is_string($v)) {
                        if (in_array(strtoupper($v), $this->keyValues)) {
                            $sWhere .= " {$field} {$operator} {$this->valuesMap[strtoupper($v)]}";
                        } else {
                            $sWhere .= " {$field} {$operator} '{$v}' ";
                        }
                    } else if (is_numeric($v)) {
                        $sWhere .= " {$field} {$operator} {$v} ";
                    }

                }

            }

        } else if (in_array($key, Array("NIN", "IN"))) {
            foreach ($value as $field=>$arguments) {
                $sWhere .= $this->toInClause($field, $operator, $arguments);
            }
        } else if (in_array($key, Array("LIKE", "NLIKE"))) {
            foreach ($value as $field=>$v) {
                $sWhere .= $this->toLikeClause($field, $operator, $v);
            }
        } else if (in_array($key, Array("GT", "GTE", "LT", "LTE", "EQ", "DIF"))) {

            foreach ($value as $field=>$v) {
                if (is_numeric($v)) {
                    $sWhere .= " {$field} {$operator} {$v} ";
                } else {
                    $sWhere .= " {$field} {$operator} '{$str}' ";
                }
            }

        }
        
        
        return $sWhere;
    }
    
    private function parseField($field) {
        if (stripos($field, '$')) {
            return str_replace('$', "", $field);
        } else if (is_numeric($field) || is_bool($field)) {
            return $field;
        } else if (is_string($field)) {
            return " '{$field}' ";
        }
    }
    
    private function toLikeClause($field, $operator, $argument) {
        
        return " LOWER({$field}) {$operator} LOWER('%{$argument}%') ";
        
    }
    
    private function toInClause($field, $operator, $arguments) {
        $isNumeric = false;
        $contador = 0;

        $str = "";
        foreach ($arguments as $v) {

            $virgula = "";
            if ($contador > 0) {
                if ($isNumeric != is_numeric($v)) {
                    throw new \Exception("Clausula {$operator} inválida. Tentou mesclar valor numérico com não numérico.");
                }
                $virgula = ", ";

            } else {
                $isNumeric = is_numeric($v);
            }

            if ($isNumeric) {
                $v = "'{$v}'";
            }

            $str .= "{$virgula}{$v}";
            $contador++;
        }

        return " {$field} {$operator} ({$str}) ";
    }
    
    
    private function toSimpleClause($field, $operator, $argument) {
        $v = $this->parseField($argument);
        return " {$field} {$operator} {$v} ";
    }
    
    
    
}