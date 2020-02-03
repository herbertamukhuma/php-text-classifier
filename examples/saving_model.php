<?php

use TextClassifier\DataSet\DataSet;
use TextClassifier\Model;
use TextClassifier\TextClassifier;

require_once "../text-classifier/autoload.php";

try {
    $dataset = new DataSet("tweets", 100, 1150, true);
    $classifier = new TextClassifier($dataset, true);
    $classifier->train();

    $model = new Model($classifier);
    $model->saveModel();

} catch (\TextClassifier\Exception\DataSetException $e) {

    print_r("Error: " . $e->getMessage());

} catch (\TextClassifier\Exception\WordDictionaryException $e) {

    print_r("Error: " .$e->getMessage());

} catch (\Phpml\Exception\LibsvmCommandException $e) {

    print_r("Error: " .$e->getMessage());

} catch (\TextClassifier\Exception\ModelException $e) {

    print_r("Error: " .$e->getMessage());

}
