<?php

class  Serializer_PHP extends Serializer {

    public function serialize() {
        $this->path = Reconstruction::getPath($this->fileName, 'dataPHP');
        $this->output = serialize($this->data);
        $this->save();
    }

}

?>
