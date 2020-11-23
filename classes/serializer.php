<?php

abstract class serializer {

    protected $catalogue;
    protected $data;
    protected $fileName;
    protected $output = "";
    protected $path = null;

    public function __construct(catalogue $catalogue, $data, $fileName) {
        $this->catalogue = $catalogue;
        $this->data = $data;
        $this->fileName = $fileName;
    }

    public function serialize() {
        $this->save();
    }

    public function save() {
        file_put_contents($this->path, $this->output);
    }

}   

?>
