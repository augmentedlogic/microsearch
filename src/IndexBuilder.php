<?php
/**
 *
 * @copyright 2022 Wolfgang Hauptfleisch <dev@augmentedlogic.com>
 * MIT License
 * This file is part of microsearch
 *
 **/
namespace com\augmentedlogic\microsearch;

class IndexBuilder
{

    private $index = array();
    private $docs = array();
    private $db = array();
    private $max_token_length = 1;

    public function strpos_all($haystack, $needle) {
        $offset = 0;
        $allpos = array();
        while (($pos = strpos($haystack, $needle, $offset)) !== FALSE) {
            $offset   = $pos + 1;
            $allpos[] = $pos;
        }
    return $allpos;
    }

    public function setMaxTokenLength($max_token_length)
    {
        $this->max_token_length = $max_token_length;
    }

    public function indexDocument($doc)
    {
        $text = strtolower($doc->getText());
        $data = preg_split('/\s+/', strtolower($text));
        $counter = array();

        for ($x = 1; $x <= $this->max_token_length; $x++) {

          foreach($data as $i=>$d) {
            $token = $data[$i];
            switch($x)
            {
             case 2:
                 if(isset($data[$i+1])) {
                     $token = $data[$i] . " " . $data[$i+1];
                 }
             break;

             case 3:
                 if(isset($data[$i+1]) && isset($data[$i+2])) {
                     $token = $data[$i] . " " . $data[$i+1] . " " . $data[$i+2];
                 }
             break;
            }

            $pos = strpos($text, $token);

            $poses = $this->strpos_all($text, $token);

            if(isset($counter[base64_encode($token)])) {
              $counter[base64_encode($token)]++;
            } else {
              $counter[base64_encode($token)] = 0;
            }

            $this->docs[$doc->getDocId()] = array("properties" => $doc->getProperties(), "text" => $doc->getText());
            if(isset($poses[$counter[base64_encode($token)]])) {
                $this->index[] = array("doc_id" => $doc->getDocId(), "token" => $token, "pos" => $poses[$counter[base64_encode($token)]]);
            }
          }

        }

    }

    public function getIndex()
    {
    return $this->index;
    }

    public function saveIndex($path)
    {
        $this->db["index"] = $this->index;
        $this->db["docs"] = $this->docs;
        file_put_contents($path, gzencode(serialize($this->db)));
    }

}
