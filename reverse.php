<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $options = array('http' => array('method' => "GET", 'header' => "Accept: application/sparql-results+json\r\n"));
    $stream_context = stream_context_create($options);
    $host = 'http://localhost:3030/OmniStore/query?query=';

    if(isset($_POST['query'])) {
        $query = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX obo: <http://purl.obolibrary.org/obo/> PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> SELECT ?label WHERE { ?child rdfs:subClassOf obo:NCRO_0000025 . ?child rdfs:label ?label FILTER STRSTARTS(UCASE(?label), UCASE("'. $_POST['query'] .'")) } ORDER BY ?label';

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
            echo json_encode(array('success' => false, 'error' => 'No Results Found'));
            exit;
        }

        $i = 0;
        $html = '';

        foreach ($json['results']['bindings'] as $item) {
            $html .= '<p tabindex="' . $i++ . '">' . $item['label']['value'] . '</p>';
        }

        echo json_encode(array(
            'success' => true,
            'html' => $html
        ));
        exit;
    }
    else if(isset($_POST['gene'])) {
        $gene = $_POST['gene'];
        $page = $_POST['page'];
        $rows = $_POST['rows'];
		$sort_dir = $_POST['sort_dir'];
    $sort_col = $_POST['sort_col'];
    $selected = json_decode($_POST['selected']);
    $validation_filter = $_POST['validation_filter'];
    $database_filter = json_decode($_POST['database_filter']);
    $pubmed_filter = $_POST['pubmed_filter'];
    $mirna_pubmed_filter = $_POST['mirna_pubmed_filter'];

    // Show/Hide column flags
    $database_count = count($database_filter);
    $show_mirdb = $database_count === 0 || in_array('mirdb', $database_filter);
    $show_targetscan = $database_count === 0 || in_array('targetscan', $database_filter);
    $show_miranda = $database_count === 0 || in_array('miranda', $database_filter);
    $show_mirtarbase = $database_count === 0 || in_array('mirtarbase', $database_filter);

    // Invalid score based on sort direction
    $invalid_score = $sort_dir == 'DESC' ? -9999 : 9999;
    // Use min or max function in query base on sort direction
    $minmax = $sort_dir == 'DESC' ? 'MAX' : 'MIN';
    // CSS class based on sort direction
    $sort_class = $sort_dir == 'DESC' ? ' sorting_desc' : ' sorting_asc';

    // Having flags based on various filters
    $having = [];
    if ($pubmed_filter == 'has') $having[] = '?pubmed_ids != ""';
    else if ($pubmed_filter == 'no') $having[] = '?pubmed_ids = ""';
    if ($database_count > 0 && $show_mirdb) $having[] = '?mirdb_score != ' . $invalid_score;
    if ($database_count > 0 && $show_targetscan) $having[] = '?targetscan_score != ' . $invalid_score;
    if ($database_count > 0 && $show_miranda) $having[] = '?miranda_score != ' . $invalid_score;
    if ($database_count > 0 && $show_mirtarbase && $validation_filter == 'all') $having[] = '?mirtarbase_id != ""';
    if ($validation_filter == 'predicted') $having[] = '?mirtarbase_id = ""';
    if ($validation_filter == 'validated') $having[] = '?mirtarbase_id != ""';
    $having = count($having) == 0 ? '' : 'HAVING(' . implode(' && ', $having) . ') ';


$query = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> ' .
        'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> ' .
        'PREFIX obo: <http://purl.obolibrary.org/obo/> ' .
        'PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#> ' .
        'SELECT ?mirna_label   ' .
        
        ($show_mirdb ? '(' . $minmax . '(COALESCE(?mdb_score, ' . $invalid_score . ')) AS ?mirdb_score) ' : '') .
        ($show_targetscan ? '(' . $minmax . '(COALESCE(?ts_score, ' . $invalid_score . ')) AS ?targetscan_score) ' : '') .
        ($show_miranda ? '(' . $minmax . '(COALESCE(ABS(?mrnd_score), ' . $invalid_score . ')) AS ?miranda_score) ' : '') .
        '(GROUP_CONCAT(DISTINCT COALESCE(?mtb_id, ""); SEPARATOR="") AS ?mirtarbase_id) ' .
        '(GROUP_CONCAT(DISTINCT COALESCE(?pubmed_id, ""); SEPARATOR=",") AS ?pubmed_ids) ' .
        'WHERE { ' .
        '?target rdfs:label "' . $gene .  '" . ' .
        '?prediction obo:OMIT_0000160 ?target . ' .
        '?prediction obo:OMIT_0000159 ?mirna . ' .
        '?mirna rdfs:label ?mirna_label . ' .

        ($show_targetscan ?
            'OPTIONAL { ' .
            '?prediction rdf:type obo:OMIT_0000019 . ' .
            '?prediction obo:OMIT_0000108 ?ts_score ' .
            '} ' : '') .
        ($show_mirdb ?
            'OPTIONAL { ' .
            '?prediction rdf:type obo:OMIT_0000020 . ' .
            '?prediction obo:OMIT_0000108 ?mdb_score ' .
            '} ' : '') .
        ($show_miranda ?
            'OPTIONAL { ' .
            '?prediction rdf:type obo:OMIT_0000021 . ' .
            '?prediction obo:OMIT_0000108 ?mrnd_score ' .
            '} ' : '') .
        'OPTIONAL { ' .
        '?prediction rdf:type obo:OMIT_0000174 . ' .
        '?prediction oboInOwl:hasDbXref ?mtb_id ' .
        '} ' .
       
        '} ' .
        'GROUP BY ?mirna_label ' .
        $having;

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

       ob_start();
    include('reversetable.php');
    $html = ob_get_contents();
    ob_end_clean();
        echo json_encode(array(
            'success' => true,
            'total' => $total,
            'pages' => $pages,
            'page' => $page,
            'html' => $html
        ));
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>OmniSearch</title>

    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/main.css"/>
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/ui/" style="padding: 5px">
				<img src="/ui/images/logo.png" class="img-responsive" style="max-height: 40px"/>
			</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/ui/about.php">About</a></li>
                <li><a href="/ui/help.php">Help</a></li>
                <li><a href="/" target="_blank">Wiki/Feedback</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="header">
    <form id="search_form" autocomplete="off">
        <h2>Search for microRNA</h2>
        <div class="row">
            <div class="col-md-4 col-sm-4">
                <div class="searchbox">
                    <label for="gene">
                        Enter a Target Gene
                        <span class="glyphicon glyphicon-question-sign pull-right" style="color: yellow" data-toggle="tooltip" data-placement="left" title="Begin typing a complete gene name or part of such a name. Or simply press the down-arrow key without typing anything."></span>
                    </label>
                    <input id="gene" type="text" class="form-control" required autofocus/>
                    <div></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-default btn-block">
                    <span class="glyphicon glyphicon-search"></span><span> Search</span>
                </button>
            </div>
        </div>
    </form>
</div>
<div id="wrapper">
    <div id="error_panel"></div>
    <div id="content">
        <div id="page_controls" class="row">
            <div class="col-md-2 col-sm-2">
                <label for="rows_select">Rows per page</label>
                <select id="rows_select" class="form-control" style="width: 100%" autocomplete="off">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="all">All</option>
                </select>
            </div>
            <div class="col-md-8 col-sm-8" style="text-align: center">
                <label><span id="total_span"></span></label>
                <nav>
                    <ul class="pagination">
                        <li><button id="first_btn" type="button" onclick="first()">&laquo;</button></li>
                        <li><button id="prev_btn" type="button"  onclick="prev()">&lsaquo;</button></li>
                        <li><label>Page <span id="page_span">0</span> of <span id="pages_span">0</span></label></li>
                        <li><button id="next_btn" type="button"  onclick="next()">&rsaquo;</button></li>
                        <li><button id="last_btn" type="button"  onclick="last()">&raquo;</button></li>
                    </ul>
                </nav>
            </div>
            <div class="col-md-2 col-sm-2">
                <form id="goto_page_form" autocomplete="off">
                    <label for="page_input">Go to Page</label>
                    <div class="input-group">
                        <input id="page_input" type="text" class="form-control" required/>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">Go</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <div id="table_div" class="table-responsive">
        </div>
    </div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="reversejs.js"></script>

</body>
</html>