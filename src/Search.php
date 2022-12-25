<?php
/**
 *
 * @copyright 2022 Wolfgang Hauptfleisch <dev@augmentedlogic.com>
 * MIT License
 * This file is part of microsearch
 *
 **/
namespace com\augmentedlogic\microsearch;

use FuzzyWuzzy\Fuzz;
use FuzzyWuzzy\Process;

class Search
{

   private $index = null;
   private $fuzziness = 80;

   public function loadIndex($path)
   {
       $this->index = unserialize(file_get_contents($path));
   }

   public function setFuzziness($fuzziness)
   {
       $this->fuzziness = $fuzziness;
   }


   public function doSearch($query)
   {
      $fuzz = new Fuzz();
      //print $fuzz->ratio('this is a test', 'this is a test!');
      //print $fuzz->partialRatio('this is a test', 'this is a test!');

      $db = unserialize(file_get_contents("index.db"));
      $index = $db["index"];
      $docs = $db["docs"];
      $results = array();
      $c = 0;

      $before = 10;
      $length = 200;
      $doc_list = array();

      foreach($index as $entry) {

         if($fuzz->ratio($entry['token'], strtolower($query)) > $this->fuzziness) {

            $start = $entry["pos"] - $before;
            if($start < 0) {
               $start = 0;
            }


            $results[$c] = $entry;


            // TODO
            //$docs[$entry["doc_id"]]["text"] = substr_replace($docs[$entry["doc_id"]]["text"], "<b>", $entry["pos"], 0);
            //$docs[$entry["doc_id"]]["text"] = substr_replace($docs[$entry["doc_id"]]["text"], "</b>", $entry["pos"] + strlen($entry['token']) + 3, 0);
            $snippet = substr($docs[$entry["doc_id"]]["text"], $start, 100);


            //$snippet = str_replace("<b>".$query."</b>", $query, $snippet);
            $results[$c]["snippet"] = $snippet;
            $results[$c]["ratio"] = $fuzz->ratio($entry['token'], strtolower($query));
            //$results[$c]["partial_ratio"] = $fuzz->partialRatio($entry['token'], strtolower($query));
            $c++;
         }
      }

      usort($results, function($a, $b) {
          return $b['ratio'] <=> $a['ratio'];
      });

      //print_r($results);

      $final_results = array();
      $final_results["results"] = array();
      $final_results["query"] = $query;
      $final_results["docs_searched"] = count($docs);


      foreach($results as $i=>$result) {
         $result["properties"] = $docs[$result["doc_id"]]["properties"];

         // only of "one per doc" is enabled
         if(!in_array($result["doc_id"], $doc_list)) {
             //$final_results["results"][$result["doc_id"]]["found"][] = $result;
             $final_results["results"][$i] = $result;
             $doc_list[] = $result["doc_id"];
         }


      }

      $final_results["matches"] = count($final_results["results"]);

      return $final_results;
      }

}

