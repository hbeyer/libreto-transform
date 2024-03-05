<?php

#[\AllowDynamicProperties]
abstract class Serializer {

    protected $catalogue;
    protected $data;
    protected $fileName;
    protected $output = "";
    protected $path = null;

    public function __construct(Catalogue $catalogue, $data, $fileName) {
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
