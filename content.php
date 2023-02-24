<?php
require __DIR__ .'/vendor/autoload.php';
include('functions/encode.php');

$content = [];
foreach (glob('projectFiles/*') as $path) {
	$folder = strtr($path, ['projectFiles/' => '']);
	if ($path == "projectFiles/index.php") {
		continue;
	}
    $content[$folder] = [];
	$pathCat = $path.'/'.$folder;
	if (file_exists($pathCat.'-cat.php')) {
		require($pathCat.'-cat.php');
	}
	elseif (file_exists($pathCat.'-metadata.xml')) {
		$catalogue = uploader::readMetadata($pathCat.'-metadata.xml');
	}
	elseif (file_exists($pathCat.'.xml')) {
		$rec = new reconstruction($pathCat.'.xml', $folder, 'xml');
		$catalogue = $rec->catalogue;
		unset($rec);
	}
	if (isset($catalogue)) {
		$content[$folder]["owner"] = $catalogue->owner;
		$content[$folder]["heading"] = $catalogue->heading;
		$content[$folder]["year"] = $catalogue->year;
		$content[$folder]["description"] = $catalogue->description;
		$content[$folder]["creatorReconstruction"] = $catalogue->creatorReconstruction;
		$content[$folder]["yearReconstruction"] = $catalogue->yearReconstruction;		
	}
}
?>
<html>
	<head>
		<title>LibReTo Rekonstruktionen</title>
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
			<h1>Lokal vorhandene Bibliotheksrekonstruktionen</h1>
			
			<div class="well">Für die folgende Aufstellung werden die unter &bdquo;projectFiles&rdquo; vorhandenen Daten ausgelesen. Jeder Unterordner beinhaltet eine Bibliotheksrekonstruktion, diese können durch erneutes Transformieren überschrieben oder manuell gelöscht werden.</div>
			
<?php foreach ($content as $key => $recData): ?>
			<div class="panel panel-default">
				<div class="panel-heading"><?php echo $recData['heading']; ?> (<?php echo $recData['year']; ?>)</div>
				<div class="panel-body"><?php echo $recData['description']; ?><br />
<?php if ($recData['creatorReconstruction']): ?>				
					<?php echo $recData['creatorReconstruction']; ?>, <?php echo $recData['yearReconstruction']; ?><br />
<?php endif; ?>
				<a href="projectFiles/<?php echo $key; ?>/index.php" title="">Zur Visualisierung</a></div>
			</div>
<?php endforeach; ?>				
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