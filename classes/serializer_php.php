<?php

class  serializer_php extends serializer {

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, 'dataPHP');
        $this->output = serialize($this->data);
        $this->save();
    }

}   

?>