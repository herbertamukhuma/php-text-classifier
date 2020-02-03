<?php

use TextClassifier\Model;

require_once "../text-classifier/autoload.php";

$model = new Model();

$classifier = $model->loadModel("tweets",true);

$prediction = $classifier->classify("I hate myself");
print_r($prediction);