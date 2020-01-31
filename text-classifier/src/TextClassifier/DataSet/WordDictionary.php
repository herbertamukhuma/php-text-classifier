<?php

namespace TextClassifier\DataSet;


use TextClassifier\Exception\WordDictionaryException;

class WordDictionary
{
    const DELIMITERS = [self::PADDING_DELIMITER, self::UNKNOWN_DELIMITER, self::START_DELIMITER];

    const PADDING_DELIMITER = "<PAD>";
    const UNKNOWN_DELIMITER = "<UNK>";
    const START_DELIMITER = "<START>";

    const WORD_INSERT_POSITION = 3;

    private $dictionary = [];

    private $lastError = "";

    public function __construct()
    {
        $this->dictionary = self::DELIMITERS;
    }

    /**
     * adds a word to the dictionary
     * @param $word
     * @throws WordDictionaryException
     */
    public function add($word){

        //check that word is not a delimiter
        if(in_array($word, self::DELIMITERS)){
            throw new WordDictionaryException("The provided word matches a delimiter: Word: " . $word);
        }

        $position = array_search($word, $this->dictionary);

        if($position !== false){

            /**
             * word exists in the dictionary, so don't add it, but move it to the end of the list. This will make it have a bigger index value,
             * reflective of the fact that it is repeated multiple times
            */
            array_splice($this->dictionary, $position, 1);
            $this->dictionary[] = $word;

        }else{

            //word does not exist in the dictionary, so add it at index WORD_INSERT_POSITION
            array_splice($this->dictionary, self::WORD_INSERT_POSITION, 0, $word);
        }

    }

    /**
     * returns the integer equivalent of the specified word from the dictionary
     * @param $word
     * @return false|int|string
     */
    public function getIntegerEquivalent($word){

        //check if word is in dictionary
        $position = array_search($word, $this->dictionary);

        if($position === false){

            //the word does not exists so return UNKNOWN_DELIMITER
            return array_search(self::UNKNOWN_DELIMITER, $this->dictionary);
        }else{

            //word exists, so return the index
            return $position;
        }
    }

    /**
     * @return bool
     */
    public function isValid(){

        //check if dictionary is empty
        if(count($this->dictionary) < 1){
            $this->lastError = "Empty dictionary";
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
    public function getMaxIndex(){
        return count($this->dictionary) -1;
    }

    /**
     * @return array
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }
    //end of class
}