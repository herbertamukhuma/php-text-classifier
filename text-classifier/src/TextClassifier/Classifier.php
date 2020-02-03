<?php

namespace TextClassifier;


class Classifier
{
    protected $dataset;
    protected $classifier;

    protected $trained = false;
    protected $verbose = false;

    protected $trainingStartTime;
    protected $trainingCompletionTime;
}