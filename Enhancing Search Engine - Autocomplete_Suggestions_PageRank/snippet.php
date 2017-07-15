<?php
function generateSnippet($docid, $query) {
	$doc = new DomDocument();
	$doc->loadHTMLFile($docid);
	$qarray = [];
	$qarray[0] = $query;
	$y1=explode(" ",$query);
	$qarray = array_merge($qarray,$y1);
	$snippet =null;
	try{
		foreach($doc->getElementsByTagName('meta') as $element) {
			if (strtolower($element->getAttribute('name')) == "description" || strtolower($element->getAttribute('property')) == "description") {
			$sentence = $element->getAttribute('content');
			if (stripos($sentence,$qarray[0]) !== false) {
				$snippet =  preg_replace('/[^A-Za-z0-9 .@\-]/', '', $sentence);
				return $snippet;			
			
			
		}
		}
		}
		foreach($doc->getElementsByTagName('p') as $element) {
			$sentence = $element->textContent;
			$sentence_arr = preg_split('/.*?[?.!]+\s+/',$sentence);
			foreach($sentence_arr as $s) {
				if (stripos($s,$qarray[0]) !== false) {
					$snippet =  preg_replace('/[^A-Za-z0-9 .@\-]/', '', $s);
					return $snippet;			
				}
			}
			
		}
		
		
		foreach($doc->getElementsByTagName('meta') as $element) {
			if (strtolower($element->getAttribute('name')) == "description" || strtolower($element->getAttribute('property')) == "description") {
			$sentence = $element->getAttribute('content');
			foreach($qarray as $q1){
			if (stripos($sentence,$q) !== false) {
				$snippet =  preg_replace('/[^A-Za-z0-9 .@\-]/', '', $sentence);
				return $snippet;			
				}
			}
		}
		}
		foreach($doc->getElementsByTagName('p') as $element) {
			$sentence = $element->textContent;
			for($i = 1; $i < count($qarray); $i++) {
				$sentence_arr = preg_split('/.*?[?.!]+\s+/',$sentence);
				foreach($sentence_arr as $s) {
					if (stripos($s, $qarray[$i]) !== false) {
						$snippet =  preg_replace('/[^A-Za-z0-9 .@\-]/', '', $s);
						return $snippet;			
						}
					}
				}
		}
		
		foreach($doc->getElementsByTagName('a') as $element) {
			$flaga = false;
			$sentence = strtolower($element->textContent);
			foreach($qarray as $q1){			
			if (stripos($sentence,$q1) !== false) {
				$snippet =  preg_replace('/[^A-Za-z0-9 .@\-]/', '', $sentence);
				$flaga = true;				
				return $snippet;
				}
			}
		}
		
	}catch(Exception $e){
		$snippet = "11";
	}
	return $snippet;
}
?>

