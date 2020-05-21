<?php

  class SchemaDefinition implements JsonSerializable {
    public function __construct(string $version) {
        $this->version = $version;
        $this->fields = [];
    }

    public function addField(string $name, string $type, array $optionalEnumValues = array()) {
      $newChild = array('name' => $name, 'type' => $type, 'fields'=>[]);
      if($type === 'enum') {
        $newChild['enum'] = $optionalEnumValues;
      }
      $this->fields[] = $newChild;
    }

    public function addParentField(string $name, string $type, string $parentField, array $optionalEnumValues = array()) {
      $newChild =  array('name' => $name, 'type' => $type, 'fields'=>[]);
      if($type === 'enum') {
        $newChild['enum'] = $optionalEnumValues;
      }
 
      $fake_root= array('name' => 'root', 'type' => 'object', 'fields'=>$this->fields);
      $this->appendChild($fake_root, $parentField, $newChild);
      $this->fields = $fake_root['fields'];
  
    }

    public function appendChild(&$parent, $id, $newChild){
      if($parent['name']==$id) {
        array_push($parent['fields'], $newChild);
        return; 
      }
     
      foreach ($parent['fields'] as &$field){
        $found = $this->appendChild($field, $id, $newChild);
      } 
    }  

    public function jsonSerialize() {
      return array('version' => $this->version, 'fields' => $this->fields);
    }
  }