<?php

  class SchemaDefinition implements JsonSerializable {
    public function __construct(string $version) {
        $this->version = $version;
        $this->fields = [];
    }

    public function addField(string $name, string $type, array $optionalEnumValues = array()) {
      $newField = array('name' => $name, 'type' => $type);
      if($type === 'enum') {
        $newField['enum'] = $optionalEnumValues;
      }
      $this->fields[] = $newField;
    }

    public function jsonSerialize() {
      return array('version' => $this->version, 'fields' => $this->fields);
    }
  }