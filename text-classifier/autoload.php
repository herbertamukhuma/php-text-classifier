<?php
$dir_path =  __DIR__;
$dir_path = str_replace("\\","/",$dir_path);

//PHP-ML autoload file
require_once $dir_path."/../php-ml/vendor/autoload.php";

//TextClassifier files
require_once $dir_path."/src/TextClassifier/Classifier.php";
require_once $dir_path."/src/TextClassifier/TextClassifier.php";
require_once $dir_path."/src/TextClassifier/Model.php";

require_once $dir_path."/src/TextClassifier/DataSet/DataSet.php";
require_once $dir_path."/src/TextClassifier/DataSet/WordDictionary.php";

require_once $dir_path."/src/TextClassifier/Exception/DataSetException.php";
require_once $dir_path."/src/TextClassifier/Exception/WordDictionaryException.php";
require_once $dir_path."/src/TextClassifier/Exception/ModelException.php";
require_once $dir_path."/src/TextClassifier/Exception/TextClassifierException.php";