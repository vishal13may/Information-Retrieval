<?php

if (isset($_GET["lookup"])) {
    $splitTerm = (isset($_GET["lookup"]) ? $_GET["lookup"] : null);
    $numberOfSuggestions = 0;
    $splitTerm = strtolower($splitTerm);
    $terms = explode(" ", $splitTerm);
    $numberOfTerms = sizeof($terms);
    $counter = 0;
    $removed = "";
    do{
        $splitTerm = implode(" ",array_slice($terms,$counter,$numberOfTerms));
  	$removed = implode(" ",array_slice($terms,0,$counter));      
  	$counter = $counter+1;
        $url = "http://localhost:8983/solr/irhw5/suggest?indent=on&q=*:*&suggest.q=" . urlencode($splitTerm). "&wt=json";
        $json = file_get_contents($url);
        $data = json_decode($json,true);
        $data1 = $data['suggest']['suggest'][$splitTerm]['suggestions'];
        $numberOfSuggestions = $data['suggest']['suggest'][$splitTerm]['numFound'];
    }while($numberOfSuggestions == 0 or $counter < $numberOfTerms);
    if($numberOfSuggestions > 0){
        usort($data1, function($a, $b) {
            if($a['weight']==$b['weight']) return (strlen($a['term']) - strlen($b['term']))>0?1:-1;
            return $a['weight'] < $b['weight']?1:-1;});
        foreach ($data1 as $s)
        {
            $s['term']= preg_replace('/\d+/',"",$s['term']);
	    if($counter>1){            
		$suggestions[] = $removed . " " . trim(str_replace("_","",$s['term']));}
	    else{
		$suggestions[] = trim(str_replace("_","",$s['term']));}
        }
        $suggestions = array_unique($suggestions);
        echo json_encode(array_slice($suggestions,0,5));
    }
    else{
        echo "[]";
    }
}
?>
