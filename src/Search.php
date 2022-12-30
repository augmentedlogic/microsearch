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

    private $index_db = null;
    private $fuzziness = 80;
    private $match_all = false;
    private $snippet_length_after = 100;
    private $snippet_length_before = 50;
    private $highlight_token = false;


    public function loadIndex($path)
    {
        $this->index_db = unserialize(gzdecode(file_get_contents($path)));
    }

    public function setFuzziness($fuzziness)
    {
        $this->fuzziness = $fuzziness;
    }

    public function setMatchAll($match_all)
    {
        $this->match_all = $match_all;
    }

    public function setHighlight($highlight_toke)
    {
        $this->highlight_token = $highlight_toke;
    }

    public function setSnippetPadding($before, $after)
    {
        $this->snippet_length_before = $before;
        $this->snippet_length_after = $after;
    }



    public function doSearch($query)
    {
        $fuzz = new Fuzz();
        //print $fuzz->ratio('this is a test', 'this is a test!');
        //print $fuzz->partialRatio('this is a test', 'this is a test!');

        $index = $this->index_db["index"];
        $docs = $this->index_db["docs"];
        $results = array();
        $c = 0;

        $before = $this->snippet_length_before;
        $length = $this->snippet_length_after;
        $doc_list = array();

        foreach($index as $entry) {

            if($fuzz->ratio($entry['token'], strtolower($query)) > $this->fuzziness) {

                $start = $entry["pos"] - $before;
                if($start < 0) {
                    $start = 0;
                }

            $results[$c] = $entry;

            $snippet = substr($docs[$entry["doc_id"]]["text"], $start, 100);
            if($this->highlight_token) {
                 $k = $entry["token"];
                 $snippet = preg_replace("/\w*?$k\w*/i", "<b>$0</b>", $snippet);
            }

            $results[$c]["snippet"] = $snippet;
            $results[$c]["ratio"] = $fuzz->ratio($entry['token'], strtolower($query));
            //$results[$c]["partial_ratio"] = $fuzz->partialRatio($entry['token'], strtolower($query));
            $c++;
            }
        }

        usort($results, function($a, $b) {
          return $b['ratio'] <=> $a['ratio'];
        });

        $final_results = array();
        $final_results["results"] = array();
        $final_results["query"] = $query;
        $final_results["docs_searched"] = count($docs);

        foreach($results as $i=>$result) {
            $result["properties"] = $docs[$result["doc_id"]]["properties"];

            // only of "one per doc" is enabled
            if($this->match_all) {
               $final_results["results"][$i] = $result;

            } else {

                if(!in_array($result["doc_id"], $doc_list)) {
                    $final_results["results"][$i] = $result;
                    $doc_list[] = $result["doc_id"];
                }

            }

        }

        $final_results["matches"] = count($final_results["results"]);

    return $final_results;
    }

}

