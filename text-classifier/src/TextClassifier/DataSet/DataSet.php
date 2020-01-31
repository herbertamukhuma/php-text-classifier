<?php

namespace TextClassifier\DataSet;

use FilesystemIterator;
use TextClassifier\Exception\DataSetException;

class DataSet
{
    const DEFAULT_SAMPLE_ARRAY_MAX_LENGTH = 1000;
    const DEFAULT_SAMPLE_COUNT_PER_LABEL = 80;

    private $samples = [];
    private $labels = [];

    private $datasetName;
    private $sampleArrayLength;
    private $sampleCountPerLabel;
    private $wordDictionary;

    private $verbose = false;

    private $lastError = "";

    /**
     * DataSet constructor.
     * @param null $datasetName
     * @param int $sampleArrayLength
     * @param int $sampleCountPerLabel
     * @param bool $verbose
     * @throws DataSetException
     * @throws \TextClassifier\Exception\WordDictionaryException
     */
    public function __construct($datasetName = NULL, $sampleArrayLength = self::DEFAULT_SAMPLE_ARRAY_MAX_LENGTH, $sampleCountPerLabel = self::DEFAULT_SAMPLE_COUNT_PER_LABEL,$verbose = false)
    {
        $this->sampleArrayLength = $sampleArrayLength;
        $this->sampleCountPerLabel = $sampleCountPerLabel;
        $this->verbose = $verbose;

        //initialize word dictionary
        $this->wordDictionary = new WordDictionary();

        if($datasetName !== NULL){
            $this->initializeDataSet($datasetName, $sampleArrayLength);
        }
    }

    /**
     * initializes the dataset
     * @param $datasetName
     * @param $sampleArrayLength
     * @throws DataSetException
     * @throws \TextClassifier\Exception\WordDictionaryException
     */
    private function initializeDataSet($datasetName, $sampleArrayLength){

        if($this->verbose) print("Initializing dataset\n\n");

        //get available datasets
        if($this->verbose) print("retrieving available datasets...\n");
        $datasets = $this->getDataSets();


        //check if $this->>datasetName is a valid dataset name
        if(!array_key_exists($datasetName,$datasets)){
            throw new DataSetException("Invalid dataset name: " . $datasetName);
        }

        //set class variable
        $this->datasetName = $datasetName;
        $this->sampleArrayLength = $sampleArrayLength;

        //initialize the dataset
        $dataset_path = $datasets[$datasetName];

        $iterator = new FilesystemIterator($dataset_path);

        //holds the the text documents in their raw form
        $raw_samples = array();

        if($this->verbose) print("Reading dataset ($datasetName) data\n\n");

        while ($iterator->valid()){

            if($this->verbose) print("reading file ". $iterator->getBasename() ."\n");

            //verify file type
            if($iterator->getExtension() !== "json"){
                throw new DataSetException("Unknown file type at: " . $iterator->getPathname());
            }

            //get the basename without the extension, this will act as a class label
            $label = $iterator->getBasename(".json");

            //read the content of the file
            $data = file_get_contents($iterator->getPathname());

            //decode the json data
            $json_data = json_decode($data,true);

            if($json_data === NULL){
                throw new DataSetException("Unable to json decode data in file: " . $iterator->getPathname());
            }

            //loop through $json_data
            $sample_count = 0;

            foreach ($json_data as $text){

                if($sample_count >= $this->sampleCountPerLabel){
                    break;
                }

                $raw_samples[$label][] = $text;

                $sample_count++;
            }

            $iterator->next();
        }

        //create word dictionary
        $this->populateWordDictionary($raw_samples);

        //encode the  raw samples into integer form
        $this->integerEncodeRawSamples($raw_samples);
    }

    /**
     * creates a word dictionary using raw text samples
     * @param $rawSamples
     * @throws \TextClassifier\Exception\WordDictionaryException
     */
    private function populateWordDictionary($rawSamples){

        if($this->verbose) print("Populating word dictionary\n\n");

        foreach ($rawSamples as $label => $texts){

            foreach ($texts as $text){
                $words = explode(" ", $text);

                foreach ($words as $word){
                    if(!empty($word)){
                        $this->wordDictionary->add(trim($word));
                    }
                }
            }
        }

    }

    /**
     * encodes raw samples into integer values, and populates the samples and labels class variables
     * @param $rawSamples
     */
    private function integerEncodeRawSamples($rawSamples){

        if($this->verbose) print("Integer encoding raw samples \n\n");

        foreach ($rawSamples as $label => $texts){

            foreach ($texts as $text){

                /**
                 * $this->sampleArrayLength + 1 because the last element will contain the rest of the string, if the word count is greater than
                 * $this->sampleArrayLength. This last index will be removed (the index containing the rest of the string)
                */
                $words = explode(" ", $text, $this->sampleArrayLength + 1);
                $words_length = count($words);

                if($words_length > $this->sampleArrayLength){

                    //pop the last value, since it only contains the rest of the string that could not be exploded due to the imposed limit
                    array_pop($words);
                }else{

                    //add padding if $words_length is less than $this->sampleArrayLength
                    if($words_length < $this->sampleArrayLength){
                        $words = array_pad($words, $this->sampleArrayLength, WordDictionary::PADDING_DELIMITER);
                    }
                }

                /**
                 * at this point, $words is of length $this->sampleArrayLength. Since we need to add a start delimiter, we need to pop it, then
                 * add the start delimiter at the beginning
                */
                array_pop($words);
                array_unshift($words, WordDictionary::START_DELIMITER);

                //encode words array with integers
                foreach ($words as $key => $word){
                    $words[$key] = $this->wordDictionary->getIntegerEquivalent($word);
                }

                //populate samples and labels class variables
                $this->labels[] = $label;
                $this->samples[] = $words;

            }
        }
    }

    /**
     * returns an array (key, value pair) of the available datasets, with the name of the dataset as the key, and its
     * path as the value
    */
    public function getDataSets(){

        $dataSetsPath = $this->getDataSetsFolderPath();

        $dataSets = array();

        $iterator = new FilesystemIterator($dataSetsPath);

        while ($iterator->valid()){
            $dataSets[$iterator->getBasename()] = $iterator->getPath() . "/" . $iterator->getBasename();
            $iterator->next();
        }

        return $dataSets;
    }

    /**
     * @return mixed
     */
    public function getDataSetsFolderPath(){
        $path = dirname(__DIR__);
        $path = realpath($path . "/../../assets/datasets");
        return str_replace("\\","/",$path);
    }

    /**
     * @return array
     */
    public function getSamples()
    {
        return $this->samples;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return WordDictionary
     */
    public function getWordDictionary()
    {
        return $this->wordDictionary;
    }

    /**
     * @return bool
     */
    public function isValid(){

        //check that $samples and $labels class variables are not empty
        if(count($this->samples) < 1 || count($this->labels) < 1){
            $this->lastError = "samples or labels array is empty. Samples Count: " . count($this->samples) . ", Labels Count: " . count($this->labels);
            return false;
        }

        //check if $samples and $labels arrays are of equal size
        if(count($this->samples) !== count($this->labels)){
            $this->lastError = "samples or labels arrays must be equal. Samples Count: " . count($this->samples) . ", Labels Count: " . count($this->labels);
            return false;
        }

        //check if the word dictionary is valid
        if(!$this->wordDictionary->isValid()){
            $this->lastError = $this->wordDictionary->getLastError();
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @return int
     */
    public function getSampleArrayLength()
    {
        return $this->sampleArrayLength;
    }
    //end of class
}