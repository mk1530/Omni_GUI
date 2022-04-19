<?php

// Try
try {
	// Turn off error reporting
	error_reporting(0);

	// Set the date timezone
    date_default_timezone_set('America/New_York');
	
	// Set the request header options to accept sparql-results+json
	$options = array('http' => array('method' => "GET", 'header' => "Accept: application/sparql-results+json\r\n"));

	// Create the stream context
	$stream_context = stream_context_create($options);
	
	// The SPARQL host url
    // $host = 'http://localhost:8890/sparql?query=';
    $host = 'http://localhost:3030/OmniStore/query?query=';

    // Get query parameters
    $type = $_GET['type'];
    $mirna = $_GET['mirna'];
    $mesh = $_GET['mesh'];
	$gene=$_GET['gene'];
		$match_meshexactly=$_GET['match_meshexactly'];


    if( isset($_GET['mirna'])) {
        // If type is mirna
        if ($type == 'mirna') {
            // Build the query string
            $query = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> ' .
                'PREFIX obo: <http://purl.obolibrary.org/obo/> ' .
                'PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> ' .
                'SELECT ?label ' .
                'WHERE { ' .
                '?child rdfs:subClassOf obo:NCRO_0000810 . ' .
                '?child rdfs:label ?label ' .
                'FILTER REGEX(?label, "' . $mirna . '"^^xsd:string, "i") ' .
                '} ' .
                'ORDER BY ?label';
        } // Else
        else {
            // Build the query string
            $query = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> ' .
                'PREFIX obo: <http://purl.obolibrary.org/obo/> ' .
                'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ' .
                'PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> ' .
                'SELECT ?plabel (GROUP_CONCAT(DISTINCT ?clabel; SEPARATOR=";") AS ?children) ' .
                'WHERE { ' .
                 '?parent rdfs:label ?plabel . ' .
				(($match_meshexactly=='beginningmatch') ?
            'FILTER REGEX(?plabel, "^' . str_replace('(','[(]',str_replace (')','[)]',$mesh)) . '"^^xsd:string, "i") ' .
           
             '' : 'FILTER REGEX(?plabel, "' . str_replace('(','[(]',str_replace (')','[)]',$mesh)) . '"^^xsd:string, "i")') .
                
                '?parent (rdfs:subClassOf)+ obo:OMIT_0000110 . ' .
                'OPTIONAL { ' .
                '?child rdfs:subClassOf ?parent . ' .
                '?child rdfs:label ?clabel . ' .
              
                '} ' .
                '} ' .
                'GROUP BY ?plabel ' .
                'ORDER BY ?plabel ' .
                'LIMIT 20 ';
        }

    }
    if( isset($_GET['gene'])) {
        $gene = $_POST['gene'];
        $page = $_POST['page'];
        $rows = $_POST['rows'];

        $query = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX obo: <http://purl.obolibrary.org/obo/> PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#> SELECT ?mirna_label (MAX(COALESCE(?mdb_score, 0)) AS ?mirdb_score) (MAX(COALESCE(?ts_score, 0)) AS ?targetscan_score) (MAX(COALESCE(ABS(?mrnd_score), 0)) AS ?miranda_score) (GROUP_CONCAT(COALESCE(?mtb_id, ""); SEPARATOR="") AS ?mirtarbase_id) WHERE { ?target rdfs:label "' . $gene . '" . ?prediction obo:OMIT_0000160 ?target . ?prediction obo:OMIT_0000159 ?mirna . ?mirna rdfs:label ?mirna_label . OPTIONAL { ?prediction rdf:type obo:OMIT_0000019 . ?prediction obo:OMIT_0000108 ?ts_score } OPTIONAL { ?prediction rdf:type obo:OMIT_0000020 . ?prediction obo:OMIT_0000108 ?mdb_score } OPTIONAL { ?prediction rdf:type obo:OMIT_0000021 . ?prediction obo:OMIT_0000108 ?mrnd_score } OPTIONAL { ?prediction rdf:type obo:OMIT_0000174 . ?prediction oboInOwl:hasDbXref ?mtb_id } } GROUP BY ?mirna_label';

        $url = $host . urlencode($query);

        if (($json = file_get_contents($url, false, $stream_context)) === false) {
            echo json_encode(array('success' => false, 'error' => 'Server Timeout / Host Unreachable'));
            exit;
        }

        if (($json = json_decode($json, true)) === null) {
            echo json_encode(array('success' => false, 'error' => 'json decode error'));
            exit;
        }

        if (count($json['results']['bindings']) == 0 || empty($json['results']['bindings'][0])) {
            echo json_encode(array('success' => false, 'error' => $error));
            exit;
        }

        $total = count($json['results']['bindings']);
        $rows = $rows == 'all' ? $total : $rows;
        $pages = ceil($total / $rows);
        $page = min($page, $pages);

        $offset = $rows == 'all' ? '' : 'OFFSET ' . (($page - 1) * $rows) . ' ';
        $limit = $rows == 'all' ? '' : 'LIMIT ' . $rows . ' ';

        $query = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX obo: <http://purl.obolibrary.org/obo/> PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#> SELECT ?mirna_label (MAX(COALESCE(?mdb_score, 0)) AS ?mirdb_score) (MAX(COALESCE(?ts_score, 0)) AS ?targetscan_score) (MAX(COALESCE(ABS(?mrnd_score), 0)) AS ?miranda_score) (GROUP_CONCAT(COALESCE(?mtb_id, ""); SEPARATOR="") AS ?mirtarbase_id) WHERE { ?target rdfs:label "' . $gene . '" . ?prediction obo:OMIT_0000160 ?target . ?prediction obo:OMIT_0000159 ?mirna . ?mirna rdfs:label ?mirna_label . OPTIONAL { ?prediction rdf:type obo:OMIT_0000019 . ?prediction obo:OMIT_0000108 ?ts_score } OPTIONAL { ?prediction rdf:type obo:OMIT_0000020 . ?prediction obo:OMIT_0000108 ?mdb_score } OPTIONAL { ?prediction rdf:type obo:OMIT_0000021 . ?prediction obo:OMIT_0000108 ?mrnd_score } OPTIONAL { ?prediction rdf:type obo:OMIT_0000174 . ?prediction oboInOwl:hasDbXref ?mtb_id } } GROUP BY ?mirna_label ORDER BY ASC(?mirna_label) ' . $offset . $limit;

    }

        // Build the query url
    $url = $host . urlencode($query);

    // If the query failed
    if (($json = file_get_contents($url, false, $stream_context)) === false) {
        // Inform the user that the store is unavailable
        echo json_encode(array('success' => false, 'error' => 'OmniStore Unavailable'));
        exit;
    }

    // If decoding the json string failed
    if (($json = json_decode($json, true)) === null) {
        // Inform the user that the store is unavailable
        echo json_encode(array('success' => false, 'error' => 'OmniStore Unavailable'));
        exit;
    }

    // If zero results were returned
    if(count($json['results']['bindings']) == 0 || empty($json['results']['bindings'][0])) {
        // Inform the user that no results were found
        echo json_encode(array('success' => false, 'error' => 'No Results Found'));
        exit;
    }

    // Holds index
    $i = 0;
    // Holds html markup
    $html = '';

    // Loop through the results
    foreach ($json['results']['bindings'] as $item) {
        // If type is mirna
        if($type == 'mirna') {
            // Concatenate a paragraph element with the current tab index and label
            $html .= '<p tabindex="' . $i++ . '">' . $item['label']['value'] . '</p>';
        }
        // Else
        else {
            // If there are no children mesh terms
            if(isset($item['children'])) {
                // Split the children string into an array using a semicolon as the delimiter
                $children = explode(';', $item['children']['value']);
                // Add a paragraph, including a tab index
                $html .= '<p tabindex="' . $i++ . '">' . $item['plabel']['value'] . '</p>';
                // Loop through the children
                foreach ($children as $child)
                    // Add a paragraph, including a tab index and the child class
                    $html .= '<p tabindex="' . $i++ . '" class="child">' . $child . '</p>';
            }
            // Else
            else {
                $html .= '<p tabindex="' . $i++ . '">' . $item['plabel']['value'] . '</p>';
            }
        }
    }

    // Echo the results to the client
    echo json_encode(array('success' => true, 'html' => $html));
    exit;
}
// Catch
catch(Exception $ex) {
    // Log the error
    error_log($ex);

    // Inform the user that the store is unavailable
    echo json_encode(array('success' => false, 'error' => 'OmniStore Unavailable'));
    exit;
}
?>
