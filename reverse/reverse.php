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

        $i = ($page - 1) * $rows;
        $html = '<table class="table table-bordered">
                    <thead>
                        <tr>

                            <th>All</th>
                            <th>microRNA Name</th>
                            <th>miRDB</th>
                            <th>TargetScan</th>
                            <th>miRanda</th>
                            <th>miRTarBase</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($json['results']['bindings'] as $item) {
            $html .= '<tr>' .
                '<td><p>' . ++$i . '</p></td>' . 
                '<td><p>' . $item['mirna_label']['value'] . '</p></td>' .
                '<td><p>' . ($item['mirdb_score']['value'] == 0 ? '-' : round($item['mirdb_score']['value'])) . '</p></td>' .
                '<td><p>' . ($item['targetscan_score']['value'] == 0 ? '-' : $item['targetscan_score']['value']) . '</p></td>' .
                '<td><p>' . ($item['miranda_score']['value'] == 0 ? '-' : $item['miranda_score']['value']) . '</p></td>' .
                '<td><p>' . ($item['mirtarbase_id']['value'] == '' ? '-' : $item['mirtarbase_id']['value']) . '</p></td>' .
                '</tr>';
        }


        $html .= '</tbody>
                </table>';

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
	<div id="result_controls">
        <a id="rna_central_link" href="" target="_blank" class="btn btn-default">
            <span class="glyphicon glyphicon-link"></span> RNAcentral
        </a>
        <button class="btn btn-default" type="button" onclick="$('#filter_panel').hide(); $('#download_panel').hide(); $('#analysis_panel').toggle()">
            <span class="glyphicon glyphicon-stats"></span> Perform Analysis
        </button>
        <button class="btn btn-default" type="button" onclick="$('#filter_panel').hide(); $('#analysis_panel').hide(); $('#download_panel').toggle()">
            <span class="glyphicon glyphicon-download-alt"></span> Download Results
        </button>
        <button class="btn btn-default" type="button" onclick="reset()">
            <span class="glyphicon glyphicon-remove"></span> Clear Results
        </button>
    </div>
       
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
    <div id="download_panel" class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th width="50%">Selection</th>
                <th width="50%">File Format</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div>
                        <div class="radio">
                            <label><input type="radio" name="download_radio" value="all" autocomplete="off" checked>Download the whole table (<span id="download_all_lbl">0</span>)</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="download_radio" value="partial" autocomplete="off" disabled>Download the partial table with selected targets (<span id="download_partial_lbl">0</span>)</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="download_radio" value="selected" autocomplete="off" disabled>Download selected targets only (<span id="download_selected_lbl">0</span>)</label>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <div class="radio">
                            <label><input type="radio" name="format_radio" value="tsv" autocomplete="off" checked>Tab-delimited text</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="format_radio" value="csv" autocomplete="off">CSV format</label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button id="download_btn" class="btn btn-default" type="button">Download</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

	<div id="filter_panel" class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Data Source Filter</th>
                <th>Validation Filter</th>
                <th>Publications Filter</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="database_filter" value="mirdb" autocomplete="off" checked>miRDB</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="database_filter" value="targetscan" autocomplete="off" checked>TargetScan</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="database_filter" value="miranda" autocomplete="off" checked>miRanda</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="database_filter" value="mirtarbase" autocomplete="off" checked>miRTarBase</label>
                        </div>
                    </div>
                </td>
                <td rowspan="2">
                    <div>
                        <div class="radio">
                            <label><input type="radio" name="validation_filter" value="all" autocomplete="off" checked>Show All</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="validation_filter" value="predicted" autocomplete="off">Show Predicted Targets Only</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="validation_filter" value="validated" autocomplete="off">Show Validated Targets Only</label>
                        </div>
                    </div>
                </td>
                <td rowspan="2">
                    <div>
                        <div class="radio">
                            <label><input type="radio" name="pubmed_filter" value="all" autocomplete="off" checked>Show All</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="pubmed_filter" value="no" autocomplete="off">Without Publications</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="pubmed_filter" value="has" autocomplete="off">With Publications</label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div>
                        <div class="radio">
                            <label><input type="radio" name="database_operator" value="any" autocomplete="off" checked>Show targets appearing in ANY selected source</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="database_operator" value="all" autocomplete="off">Show targets appearing in ALL selected sources</label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <button id="apply_btn" type="button" class="btn btn-default" onclick="applyFilters()" autocomplete="off" disabled>Apply Selected Filters</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
	</div>

	<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="reversejs.js"></script>

</body>
</html>