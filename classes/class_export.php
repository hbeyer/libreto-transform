<?php

class export {
	
protected $content;
protected $reconstruction;

public function save($path) {

	file_put_contents($path, $this->content);
	return(true);

	}

}

?>