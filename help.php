<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>OmniSearch - Help</title>

    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/main.css"/>
	
	<script src="https://use.fontawesome.com/8e9ce02343.js"></script>
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
    <div class="section">
        <h1>Help</h1>
    </div>
</div>
<div class="container" style="padding: 24px">
	<h2>Video Tutorial</h2>
	<a href="/ui/tutorial.php" target="_blank" class="fa fa-film fa-4x" style="text-decoration: none" title="Video Tutorial"></a>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="https://www.youtube.com/watch?v=kCFm4YkNvEg" target="_blank" class="fa fa-youtube fa-4x" style="text-decoration: none" title="YouTube Channel"></a>
	<br/>
	<br/>
	<h2>Frequently Asked Questions</h2>
	<a href="#help0">Which browsers are supported by OmniSearch?</a><br/>
    <a href="#help1">How do I search for microRNA targets?</a><br/>
    <a href="#help2">How do I filter results to show only those from certain data sources?</a><br/>
    <a href="#help3">How do I filter results to show only predicted or validated targets?</a><br/>
    <a href="#help4">How do I filter results to show only those with publications?</a><br/>
    <a href="#help5">How do I perform analysis on selected targets?</a><br/>
    <a href="#help6">How do I download results?</a><br/>
</div>
<div class="container">
    <div id="help0" class="anchor">
        <h3>Which browsers are supported by OmniSearch?</h3>
		<img src="/ui/images/supported-browsers.png" class="img-responsive" style="width: 25%"/>
		<h4>You can access OmniSearch using the following browsers:</h4>
		<ul>
			<li>Google Chrome</li>
			<li>Microsoft Edge</li>
			<li>Mozilla Firefox</li>
			<li>Opera</li>
			<li>Safari</li>
		</ul>
    </div>
    <br/>
    <div id="help1" class="anchor">
        <h3>How do I search for microRNA targets?</h3>
        <ol style="padding-left: 16px">
            <li>Start by clicking in the input field labeled "Enter a microRNA name."</li>
            <li>Begin typing the name of a microRNA or simply press the down arrow key to view the list of all microRNA names.</li>
            <li>Press Enter or Tab will select the highlighted microRNA; or, simply click on a microRNA and select it.</li>
            <li>Finally, click on the "Search" button.</li>
        </ol>
        <img src="/ui/images/help-1.png" class="img-responsive" style="border: 1px solid teal"/>
    </div>
    <br/>
    <div id="help2" class="anchor">
        <h3>How do I filter results to show only those from certain data sources?</h3>
        <ol style="padding-left: 16px">
            <li>Start by clicking on the "Filters" button next to the "Search" button.</li>
            <li>In the filters panel, select one or more data source checkboxes under "Data Source Filter."</li>
            <li>Click the "Apply Selected Filters" button at the bottom of the filters panel.</li>
        </ol>
        <img src="/ui/images/help-2.png" class="img-responsive" style="border: 1px solid teal"/>
    </div>
    <br/>
    <div id="help3" class="anchor">
        <h3>How do I filter results to show only predicted or validated targets?</h3>
        <ol style="padding-left: 16px">
            <li>Start by clicking on the "Filters" button next to the "Search" button.</li>
            <li>In the filters panel, select the appropriate radio button under "Validation Filter."</li>
            <li>Click the "Apply Selected Filters" button at the bottom of the filters panel.</li>
        </ol>
        <img src="/ui/images/help-3.png" class="img-responsive" style="border: 1px solid teal"/>
    </div>
    <br/>
    <div id="help4" class="anchor">
        <h3>How do I filter results to show only those with publications?</h3>
        <ol style="padding-left: 16px">
            <li>Start by clicking on the "Filters" button next to the "Search" button.</li>
            <li>In the filters panel, select the "With Publications" radio button under "Publications Filter."</li>
            <li>Click the "Apply Selected Filters" button at the bottom of the filters panel.</li>
        </ol>
        <img src="/ui/images/help-4.png" class="img-responsive" style="border: 1px solid teal"/>
    </div>
    <br/>
    <div id="help5" class="anchor">
        <h3>How do I perform analysis on selected targets?</h3>
        <ol style="padding-left: 16px">
            <li>Select one or more targets by clicking the checkboxes in the far left column of the results table.</li>
            <li>Click on the "Perform Analysis" button above the results table.</li>
            <li>In the perform analysis panel, select the type of analysis to be performed for the chosen analysis tool.</li>
            <li>Click the "Analyze" button for the chosen analysis tool.</li>
        </ol>
        <img src="/ui/images/help-5.png" class="img-responsive" style="border: 1px solid teal"/>
    </div>
    <br/>
    <div id="help6" class="anchor">
        <h3>How do I download results?</h3>
        <ol style="padding-left: 16px">
            <li>Select one or more targets by clicking the checkboxes in the far left column of the results table (skip this step if downloading the whole table).</li>
            <li>Click on the "Download Results" button above the results table.</li>
            <li> In the download results panel, select the appropriate radio button in the "Selection" column (the “File Format” column is disabled when “Download targets only” is chosen).</li>
            <li>Click the "Download" button at the bottom of the download results panel.</li>
        </ol>
        <img src="/ui/images/help-6.png" class="img-responsive" style="border: 1px solid teal"/>
    </div>
	<br/>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>