<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>API</title>
  <link href='http://cdn.jsdelivr.net/g/yasqe@2.2(yasqe.min.css),yasr@2.4(yasr.min.css)' rel='stylesheet' type='text/css'/>
</head>
<body>
  <div id="yasqe"></div>
  <div id="yasr"></div>
  
  <script src='http://cdn.jsdelivr.net/yasr/2.4/yasr.bundled.min.js'></script>
  <script src='http://cdn.jsdelivr.net/yasqe/2.2/yasqe.bundled.min.js'></script>
  <script>
	YASQE.defaults.sparql.endpoint = "http://omnisearch.soc.southalabama.edu:3030/OmniStore/query";
  
	var yasqe = YASQE(document.getElementById("yasqe"), {
		sparql: {
			showQueryButton: true
		}
	});
	var yasr = YASR(document.getElementById("yasr"), {
		//this way, the URLs in the results are prettified using the defined prefixes in the query
		getUsedPrefixes: yasqe.getPrefixesFromQuery
	});

	//link both together
	yasqe.options.sparql.callbacks.complete = yasr.setResponse;
  </script>
</body>