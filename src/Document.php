<?php
/**
 *
 * @copyright 2022 Wolfgang Hauptfleisch <dev@augmentedlogic.com>
 * MIT Licence
 * This file is part of microsearch
 *
 **/
namespace com\augmentedlogic\microsearch;

class Document
{
    private $doc = null;

    function __construct()
    {
        $this->doc = array();
        $this->doc["props"] = array();
    }

    public function setText($text)
    {
        $this->doc["text"] = $text;
    }

    public function setDocId($doc_id)
    {
        $this->doc["doc_id"] = $doc_id;
    }

    public function setProperty($key, $value)
    {
        $this->doc["props"][$key] = $value;
    }

    public function getDocId()
    {
    return $this->doc["doc_id"];
    }

    public function getProperties()
    {
    return $this->doc["props"];
    }

    public function getText()
    {
    return $this->doc["text"];
    }

}
