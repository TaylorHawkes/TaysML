<?php
namespace TaysML;

class Network
{
    public $layers=[];
    public $learingRate = .01;
    
    public function __construct(){

    }

    public function init2(){

      $loader = new SerpsDataLoader(); 
      [$images,$labels]=$loader->get_data();

      //print_r($labels[0]);die;

      $outputCount = count($labels[0]);
    //foreach($images as $l){
    //    echo count($l)."\n";
    //}

  //  $images=$loader->readImages("data/train-images-idx3-ubyte");
  //  $labels=$loader->readLabels("data/train-labels-idx1-ubyte");

        $this->layers[] = null; 
        $this->layers[] = new Layer(100,50);
        $this->layers[] = new Layer(50,$outputCount); 
        $this->train($images,$labels);
    }

    public function init(){

      $loader = new DataLoader(); 
      $images=$loader->readImages("data/train-images-idx3-ubyte");
      $labels=$loader->readLabels("data/train-labels-idx1-ubyte");
      
      //todo: Need to cast to float, is there better approach here?
      foreach($images as $k=>$image){
          foreach($image as $j=>$pixel){
              $images[$k][$j]=(float) $pixel;
          }
      }

      $labelDefault=[0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0,0.0];
      foreach($labels as $k=>$label){
          $labels[$k]=$labelDefault;
          $labels[$k][$label]=1.0;
      }

      $this->layers[] = null; 
      $this->layers[] = new Layer(784,16);
      $this->layers[] = new Layer(16,10); 
      $this->train($images,$labels);
    }

    public function random_subset($images,$labels,$size){

            $keys=array_rand($images,$size);
            $imagesR=[];
            $labelsR=[];
            foreach($keys as $key){
                $imagesR[]=$images[$key];
                $labelsR[]=$labels[$key];
            }

        return [$imagesR,$labelsR];
    }

    public function train($allImages,$allLabels){
        //100 trainding itersations
        $trainingIterations = 10000;
        $epoch=0;
        $error = 1000000;

        while ($epoch < $trainingIterations && $error > 0){
            $epoch++;
            $costs = [];
            [$images,$labels]=$this->random_subset($allImages,$allLabels,100);
            $labels = new \CArray($labels);
            foreach($images as $i=>$input){
                $this->layers[0]=new Layer($input);
                $prediction = $this->doForwardPropagation();
                $costs[] = $this->computeCost($prediction, $labels[$i]);
                $this->doBackPropagation($labels[$i]);
                $this->updateParameters();
            }
            $error = array_sum($costs) / count($costs);
            echo $error."\n";
        }
        $this->save();
    }

    public function save(){
       file_put_contents("data/layers.json",json_encode($this->layers));
    }

    public function load(){
       $this->layers=json_decode(file_get_contents("data/layers.json"));
    }

    public function updateParameters(){
        foreach($this->layers as $i=>$layer){
            if(!$i){continue;} //skip input layer
            $this->layers[$i]->weights -=  ($this->layers[$i]->dWeights*$this->learingRate);
        }
    }

    public function doBackPropagation($label){
        //first update the output layer
        $outputIndex= count($this->layers)-1;
        $outputlayer= $this->layers[$outputIndex];

        $dA=2 * ($outputlayer->activations-$label);
        $dZ= $dA * $this->matrix_inverse_sigmoid($outputlayer->activations); 

        $this->layers[$outputIndex]->dZ = $dZ; 

        
        $this->layers[$outputIndex]->dWeights= \CArray::matmul(
            \CArray::transpose(\CArray::atleast_2d($dZ)) ,
            \CArray::atleast_2d($this->layers[$outputIndex-1]->activations)
        );
        
        for($i = $outputIndex; $i >= 0; $i--){
            if($i==$outputIndex){continue;} //skip ouput layer 
            if(!$i){continue;} //skip input layer

            $dA2= \CArray::matmul($this->layers[$i+1]->dZ,$this->layers[$i+1]->weights);
            $dZ2= $dA2 * $this->matrix_inverse_sigmoid($this->layers[$i]->activations); 
            $this->layers[$i]->dZ = $dZ2; 

            $this->layers[$i]->dWeights= \CArray::matmul(
                \CArray::transpose(\CArray::atleast_2d($dZ2)) ,
                \CArray::atleast_2d($this->layers[$i-1]->activations)
            );
        }
        
        
    }

    public function doForwardPropagation(){
        foreach($this->layers as $i=>$layer){
            if(!$i){continue;} //skip input layer
                $this->layers[$i]->activations = $this->matrix_sigmoid(\CArray::sum($layer->weights * $this->layers[$i-1]->activations,1));
        }
        return  $this->layers[count($this->layers)-1]->activations;
    }

    //note label is in and prediction is array
    public function computeCost($prediction,$label){
       $cost =\CArray::sum(\CArray::power(($prediction-$label),2))->toArray();
       return $cost / count($prediction);
    }


    //grepper php inverse sigmoid function
    public function inverse_sigmoid($v){
         return $v * (1 - $v);
    }
    //end grepper
    
    public function sigmoid($t){
        return 1 / (1 + exp(-$t));
    }

    public function matrix_sigmoid($t){
        return 1.0 / (1.0 + \CArray::exp(-$t));
    }

    public function matrix_inverse_sigmoid($v){
         return $v * (1.0 - $v);
    }

}


