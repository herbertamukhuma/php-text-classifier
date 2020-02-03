<?php

namespace TextClassifier;


use TextClassifier\Exception\ModelException;

class Model
{
    const MODEL_FILE_EXTENSION = ".model";

    private $classifier = NULL;

    private $verbose = false;

    /**
     * Model constructor.
     * @param TextClassifier|NULL $classifier
     * @param bool $verbose
     */
    public function __construct(TextClassifier $classifier = NULL, $verbose = false)
    {
        if($classifier !== NULL){
            $this->classifier = $classifier;
        }
    }

    /**
     * @param TextClassifier|null $classifier
     * @param bool $verbose
     */
    public function setClassifier(TextClassifier $classifier, $verbose = false)
    {
        $this->verbose = $verbose;
        $this->classifier = $classifier;
    }

    /**
     * @param null $filename
     * @return bool
     * @throws ModelException
     */
    public function saveModel($filename = NULL){

        if($this->verbose) print_r("\nSaving model....\n");

        //check if the classifier is NULL
        if($this->classifier === NULL) {
            throw new ModelException("Classifier not set. Use the __construct() or setClassifier() method to specify a classifier");
        }

        //check that the classifier has been trained
        if(!$this->classifier->isTrained()){
            throw new ModelException("Classifier not trained");
        }

        //serialize $this->classifier
        $serialized = serialize($this->classifier);

        //check the filename
        if ($filename === NULL){
            $filename = $this->getModelsFolderPath() . "/" . $this->classifier->getDataset()->getDatasetName() . self::MODEL_FILE_EXTENSION;
        }else{

            //check the file extension
            $pos = strrpos($filename, ".");

            if($pos === false){
                //add the extension
                $filename .= self::MODEL_FILE_EXTENSION;
            }else{
                //check the extension is self::MODEL_FILE_EXTENSION;
                $extension = substr($filename,$pos);

                if($extension !== self::MODEL_FILE_EXTENSION){
                    throw new ModelException("The model filename extension must be " . self::MODEL_FILE_EXTENSION . ", got : $extension");
                }
            }

        }

        //save the file
        file_put_contents($filename, $serialized);

        return true;
    }

    /**
     * @param null $filename
     * @param bool $byName
     * @return mixed|TextClassifier|null
     * @throws ModelException
     */
    public function loadModel($filename = NULL , $byName = false){

        //if $byName is set to true, generate full path to file
        if($byName){
            $filename = $this->getModelsFolderPath() . "/" . $filename . self::MODEL_FILE_EXTENSION;
        }

        //check if the file exists
        if(!file_exists($filename)){
            throw new ModelException("The specified model file does not exist, got: $filename");
        }

        //check the file extension
        $pos = strrpos($filename, ".");

        if($pos === false){
            throw new ModelException("Invalid model file extension, got: $filename");
        }

        $extension = substr($filename,$pos);

        if($extension !== self::MODEL_FILE_EXTENSION){
            throw new ModelException("Invalid model file extension, got: $filename, expected: " . self::MODEL_FILE_EXTENSION);
        }

        //load the classifier
        $serialized = file_get_contents($filename);
        @$unserialized = unserialize($serialized);

        if($unserialized === FALSE){
            throw new ModelException("Unable to unserialize model file");
        }

        $this->classifier = $unserialized;

        return $this->classifier;
    }

    public function getModelsFolderPath(){
        $path = dirname(__DIR__);
        $path = realpath($path . "/../assets/models");
        return str_replace("\\","/",$path);
    }

    //end of class
}