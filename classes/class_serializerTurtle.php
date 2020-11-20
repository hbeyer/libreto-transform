<?php

class  serializerTurtle extends serializerRDF {

    public function serialize() {
        $this->path = reconstruction::getPath($this->fileName, $this->fileName, 'ttl');
        $this->makeGraph();
        $this->generateOutput();
        $this->save();
    }

    protected function generateOutput() {
        $serialiserTTL = new EasyRdf_Serialiser_Turtle;
        $this->output = $serialiserTTL->serialise($this->graph, 'turtle');
    }

}   

?>
