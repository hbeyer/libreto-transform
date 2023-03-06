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
<?php 	$active = basename(__FILE__, '.php');
		include('templates/user_interface/navigation.phtml'); 
?>
		<div class="container" style="min-height:1000px;margin-top:80px;">
			<h1>Testseite f&uuml;r LibReTo</h1>
			<p>
			<?php echo 'Es funktioniert!<br>Server: '.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'<br>PHP-Version: '.phpversion(); ?>
			</p>
			<p>Transformationsskripte können im Hauptverzeichnis (i. d. R. &bdquo;libreto-transform&rdquo;) gespeichert und über die Adressleiste ausgeführt werden. Die Ergebnisse werden im Ordner &bdquo;projectFiles&rdquo; gespeichert. Codebeispiel:
				<pre>
require __DIR__ .'/vendor/autoload.php';
include('functions/encode.php');

$reconstruction = new Reconstruction('source/myproject.xml', 'myproject', 'xml');
$reconstruction->enrichData();
$reconstruction->saveAllFormats();

$pages = array('histSubject', 'persName', 'gender', 'beacon', 'year', 'subjects', 'languages', 'placeName', 'publishers');
$doughnuts = array('histSubject', 'subjects', 'beacon', 'languages');
$clouds = array('publishers', 'subjects', 'persName', 'shelfmarkOriginal');

$facetList = new FacetList($pages, $doughnuts, $clouds);
$frontend = new Frontend($reconstruction, $facetList);
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