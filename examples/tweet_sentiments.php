<?php

use TextClassifier\DataSet\DataSet;
use TextClassifier\TextClassifier;

require_once "../text-classifier/autoload.php";

$dataset = new DataSet("tweets", 100, 1150, true);

$classifier = new TextClassifier($dataset, true);
$classifier->train();
$prediction = $classifier->classify("So boring, guess no one loves me");
print_r($prediction);
