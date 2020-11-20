<?php

class  serializerPHP extends serializer {

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, 'dataPHP');
        $this->output = serialize($this->data);
        $this->save();
    }

}   

?>
