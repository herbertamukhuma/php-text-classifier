<?php

namespace TextClassifier;


use Phpml\Classification\SVC;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\SupportVectorMachine\Kernel;
use TextClassifier\DataSet\DataSet;
use TextClassifier\DataSet\WordDictionary;
use TextClassifier\Exception\DataSetException;
use TextClassifier\Exception\TextClassifierException;

class TextClassifier extends Classifier
{

    /**
     * TextClassifier constructor.
     * @param DataSet $dataset
     * @param bool $verbose
     * @throws DataSetException
     */
    public function __construct(DataSet $dataset, $verbose = false)
    {
        //check if  dataset is valid
        if(!$dataset->isValid()){
            throw new DataSetException("Invalid dataset");
        }

        $this->verbose = $verbose;
        $this->dataset = $dataset;

        $this->classifier = new SVC(
            Kernel::RBF, // $kernel
            1000,            // $cost
            3,              // $degree
            6,           // $gamma
            0.0,            // $coef0
            0.001,          // $tolerance
            100,            // $cacheSize
            true,           // $shrinking
            true            // $probabilityEstimates, set to true
        );

    }

    /**
     * @param $text
     * @return array
     * @throws TextClassifierException
     */
    private function prepareText($text){

        if($this->verbose) print("\nPreparing sample text for prediction\n");

        //check if the text is empty
        if(empty($text)){
            throw new TextClassifierException("Cannot prepare empty text for prediction");
        }

        //get the sample array length
        $sample_array_length = $this->dataset->getSampleArrayLength();

        //explode the text into array
        $words = explode(" ", $text, $sample_array_length + 1);
        $words_length = count($words);

        if($words_length > $sample_array_length){

            //pop the last value, since it only contains the rest of the string that could not be exploded due to the imposed limit
            array_pop($words);
        }else{

            //add padding if $words_length is less than $sample_array_length
            if($words_length < $sample_array_length){
                $words = array_pad($words, $sample_array_length, WordDictionary::PADDING_DELIMITER);
            }
        }

        /**
         * at this point, $words is of length $sample_array_length. Since we need to add a start delimiter, we need to pop it, then
         * add the start delimiter at the beginning
         */
        array_pop($words);
        array_unshift($words, WordDictionary::START_DELIMITER);

        //encode words array with integers
        foreach ($words as $key => $word){
            $words[$key] = $this->dataset->getWordDictionary()->getIntegerEquivalent($word);
        }

        //divide each element in the words array by the highest WordDictionary index. This will ensure all values range from 0 to 1
        $max = $this->dataset->getWordDictionary()->getMaxIndex();

        foreach ($words as $key => $word){
            $words[$key] = $word/$max;
        }
        
        //return the encoded array
        return $words;

    }

    /**
     * trains the model
     * @throws \Phpml\Exception\LibsvmCommandException
     */
    public function train()
    {
        //get the starting time
        $this->trainingStartTime = time();

        if($this->verbose) {
            print_r("\nTraining....\n");
            print_r("starting time: " . date("Y-m-d H:i:s", $this->trainingStartTime) . "\n");
        }

        $samples = $this->dataset->getSamples();
        $labels = $this->dataset->getLabels();

       //divide each element in the samples array by the highest WordDictionary index. This will ensure all values range from 0 to 1
        $max = $this->dataset->getWordDictionary()->getMaxIndex();

        foreach ($samples as $key1 => $sample){

            foreach ($sample as $key2 => $value){
                $sample[$key2] = $value/$max;
            }

            $samples[$key1] = $sample;
        }

        $this->classifier->train($samples, $labels);

        $this->trained = true;

        //get training stop time
        $this->trainingCompletionTime = time();
        $duration = $this->trainingCompletionTime - $this->trainingStartTime;

        if($this->verbose) {
            print_r("\nTraining completed!\n");
            print_r("completion time: " . date("Y-m-d H:i:s", $this->trainingCompletionTime) . "\n");
            print_r("training duration: $duration seconds \n");
        }
    }

    /**
     * classifies the specified text
     * @param $text
     * @return array|string
     * @throws TextClassifierException
     * @throws \Phpml\Exception\LibsvmCommandException
     */
    public function classify($text){

        //check if the classifier has been trained
        if(!$this->trained){
            throw new TextClassifierException("Cannot classify based on a model that has not been trained");
        }

        //prepare text for prediction
        $words = $this->prepareText($text);

        //predict
        return $this->classifier->predictProbability($words);
    }

    /**
     * @return bool
     */
    public function isTrained()
    {
        return $this->trained;
    }

    /**
     * @return DataSet
     */
    public function getDataset()
    {
        return $this->dataset;
    }
    //end of class
}