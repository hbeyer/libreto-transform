<?php

class export {
	
protected $content;

public function save($path) {

	file_put_contents($path, $this->content);
	return(true);
}

}

?>