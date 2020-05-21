<?php

  class SchemaDefinition implements JsonSerializable {
    public function __construct(string $version) {
        $this->version = $version;
        $this->fields = [];
    }

    public function addField(string $name, string $type, array $optionalEnumValues = array()) {
      $this->fields[] = $this->generateNewChild($name, $type, $optionalEnumValues);
    }

    public function addChildField(string $name, string $type, string $parentField, array $optionalEnumValues = array()) {
      $newChild =  $this->generateNewChild($name, $type, $optionalEnumValues);
      $array_root= array('name' => 'root', 'type' => 'object', 'fields'=> $this->fields);
      $this->appendChild($array_root, $parentField, $newChild);
      $this->fields = $array_root['fields'];
    }

    private function generateNewChild(string $name, string $type, array $optionalEnumValues = array()){
      $newChild = array('name' => $name, 'type' => $type);
      if($type === 'enum') {
        $newChild['enum'] = $optionalEnumValues;
      }
      if($type == 'array<object>' or $type=='object') {
        $newChild['fields'] = [];
      }
      return $newChild;
    }

    private function appendChild(&$current, $parentFieldName, $newChild){
      if($current['name'] == $parentFieldName) {
        array_push($current['fields'], $newChild);
        return; 
      }
      if (array_key_exists('fields',$current)){
        foreach ($current['fields'] as &$field){
          $this->appendChild($field, $parentFieldName, $newChild);
        } 
      }
    }  

    public function jsonSerialize() {
      return array('version' => $this->version, 'fields' => $this->fields);
    }
  }