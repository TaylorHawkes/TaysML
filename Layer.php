<?php
namespace TaysML;

class Layer
{
    //neuron level
    public $activations;
    public $biases;
    public $dZ;
    //weight level
    public $dWeights;
    public $weights;

    //non input layer 
    public function __construct($weightsCount,$neuronsCount=false){

        $activations=[];
        $biases=[];
        $dZ=[];
        //weight level
        $weights=[];
        $dWeights=[];

            //this is first layer 
         if(is_array($weightsCount)){
            $activations=$weightsCount;
         }else{
             for ($x = 0; $x < $neuronsCount; $x++) {
                 $activations[]=0;
                 $dZ[]=0;
                 $biases[]=[rand(-1000,1000)/1000];

                 $neuronWeights=[];
                 $neurondWeights=[];
                 for($i=0;$i<$weightsCount;$i++){
                   $neuronWeights[]=rand(-1000,1000)/1000;
                   $neurondWeights[]=0;
                 }
                 $weights[]=$neuronWeights;
                 $dWeights[]=$neurondWeights;
             }
            $this->biases=new \CArray($biases);
            $this->dZ=new \CArray($dZ);
            $this->weights=new \CArray($weights);
            $this->dWeights=new \CArray($dWeights);
         }

          $this->activations=new \CArray($activations);

    }
    public function logMe(){
        echo "Activations:";
        print_r($this->activations->toArray());

        echo "dZ:";
        print_r($this->dZ->toArray());

        echo "weights:";
        print_r($this->weights->toArray());

        echo "dWeights:";
        print_r($this->dWeights->toArray());

    }


}
