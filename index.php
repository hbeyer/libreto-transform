<html>
	<head>
		<title>LibReTo Testseite</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
        <link rel="stylesheet" href="assets/css/affix.css" />
        <link rel="stylesheet" href="assets/css/proprietary.css" />
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/proprietary.js"></script>		
	</head>
	<body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container-fluid">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="https://bibliotheksrekonstruktion.hab.de" target="_blank" title="LibReTo an der HAB">HAB</a></li>
                    <li><a href="https://github.com/hbeyer/libreto-transform" title="LibReTo auf GitHub" target="_blank">GitHub</a></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Dokumentation<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="Dokumentation_CSV.pdf">Anleitung CSV</a></li>
							<li><a href="example.csv">Vorlage CSV</a></li>
							<li><a href="libreto-schema.xsd">Erfassungsschema XML</a></li>
                            <li><a href="libreto-schema-full.xsd">Erweitertes Erfassungsschema XML</a></li>
                        </ul>
                    </li>
					<?php if ($_SERVER['SERVER_PORT'] == '84'): ?>
					<li class="active"><a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>:85/" target="_blank" title="Lokale Erfassungsdatenbank">PHPMyAdmin</a></li>
					<?php endif; ?>					
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li style="margin-right:1em;"><img src="assets/images/icon.svg" height="50" alt="Logo HAB"/></li>
                </ul>
            </div>
        </nav>	
		<div class="container" style="min-height:1000px;margin-top:80px;">
			<h1>Testseite f&uuml;r LibReTo</h1>
			<p>
			<?php echo 'Es funktioniert!<br>Server: '.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'<br>PHP-Version: '.phpversion(); ?>
			</p>
			<p>Transformationsskripte können im Hauptverzeichnis (i. d. R. &bdquo;libreto-transform&rdquo;) gespeichert und über die Adressleiste ausgeführt werden. Codebeispiel:
				<pre>
require __DIR__ .'/vendor/autoload.php';
include('functions/encode.php');

$reconstruction = new reconstruction('source/myproject.xml', 'myproject', 'xml');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();

$pages = array('histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'languages', 'placeName', 'publishers');
$doughnuts = array('histSubject', 'subjects', 'beacon', 'languages');
$clouds = array('publishers', 'subjects', 'persName', 'shelfmarkOriginal');

$facetList = new facetList($pages, $doughnuts, $clouds);
$frontend = new frontend($reconstruction, $facetList);
$frontend->build();				
				</pre>
			</p>
		</div>
        <footer class="container-fluid">
            <p>
                <a href="index.php" style="color:white">Start</a>&nbsp;&nbsp;
                <a href="https://www.hab.de" style="color:white" target="_blank">HAB</a>&nbsp;&nbsp;
                <a href="http://www.mww-forschung.de" style="color:white" target="_blank">MWW</a>&nbsp;&nbsp;
            </p>
        </footer>		
	</body>
</html>