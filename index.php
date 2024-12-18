<!DOCTYPE html>
<html>
   <head>
      <title>LibReTo - Startseite</title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
      <link rel="stylesheet" href="assets/css/affix.css" />
      <link rel="stylesheet" href="assets/css/proprietary.css" />
      <link rel="stylesheet" href="assets/css/code.css" />
      <link rel="icon" type="image/x-icon" href="assets/images/favicon.png" />
      <script src="assets/js/jquery.min.js"></script>
      <script src="assets/js/bootstrap.min.js"></script>
      <script src="assets/js/proprietary.js"></script>
   </head>
   <body>
      <?php 	$active = basename(__FILE__, '.php');
         include('templates/user_interface/navigation.phtml');
         include('private/settings.php');
         ?>
      <div class="container" style="min-height:1000px;margin-top:80px;">
         <h1>Willkommen bei LibReTo!</h1>
         <p>
            <?php echo 'Server: '.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'<br>PHP-Version: '.phpversion(); ?>
         </p>
         <p>Bibliotheksrekonstruktionen können mit Hilfe des <a href="form.php" title="Formular &ouml;ffnen">Formulars</a> aus erfassten Datensammlungen erzeugt werden.</p>
         <p>Für Fortgeschrittene empfiehlt sich die Anlage von Transformationsskripten. Diese können im Hauptverzeichnis (i. d. R. &bdquo;libreto-transform&rdquo;) gespeichert und über die Adressleiste ausgeführt werden. Die Ergebnisse werden im Ordner &bdquo;projectFiles&rdquo; gespeichert. Codebeispiel:</p>
         <pre><code class="html"><span class="keyword">require</span> <span class="keyword">__DIR__</span><span class="operator">.</span><span class="prop_char">'</span><span class="string">/vendor/autoload.php</span><span class="prop_char">'</span>;
<span class="keyword">include</span>(</span><span class="prop_char">'</span><span class="string">functions/encode.php</span><span class="prop_char">'</span>);

<span class="prop_char">$</span>reconstruction <span class="operator">=</span> <span class="keyword">new</span> <span class="function">Reconstruction</span>(<span class="prop_char">'</span><span class="string">source/myproject.xml</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">myproject</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">xml</span><span class="prop_char">'</span>);
<span class="prop_char">$</span>reconstruction-><span class="function">enrichData</span>();
<span class="prop_char">$</span>reconstruction-><span class="function">saveAllFormats</span>();

<span class="prop_char">$</span>pages <span class="operator">=</span> <span class="function">array</span>(<span class="prop_char">'</span><span class="string">histSubject</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">persName</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">gender</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">beacon</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">year</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">subjects</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">languages</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">placeName</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">publishers</span><span class="prop_char">'</span>);
<span class="prop_char">$</span>doughnuts <span class="operator">=</span> <span class="function">array</span>(<span class="prop_char">'</span><span class="string">histSubject</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">subjects</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">beacon</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">languages</span><span class="prop_char">'</span>);
<span class="prop_char">$</span>clouds = <span class="function">array</span>(<span class="prop_char">'</span><span class="string">publishers</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">subjects</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">persName</span><span class="prop_char">'</span><span class="string">, </span><span class="prop_char">'</span><span class="string">shelfmarkOriginal</span><span class="prop_char">'</span>);

<span class="prop_char">$</span>facetList <span class="operator">=</span> <span class="keyword">new</span> <span class="function">FacetList</span>(<span class="prop_char">$</span>pages, <span class="prop_char">$</span>doughnuts, <span class="prop_char">$</span>clouds);
<span class="prop_char">$</span>frontend <span class="operator">=</span> <span class="keyword">new</span> <span class="function">Frontend</span>(<span class="prop_char">$</span>reconstruction, <span class="prop_char">$</span>facetList);
<span class="prop_char">$</span>frontend-><span class="function">build</span>();</code></pre>
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
