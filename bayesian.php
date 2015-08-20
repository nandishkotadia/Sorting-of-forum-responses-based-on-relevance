<?php

include('autoloader.php'); // won't include it again in the following examples
 
use NlpTools\Tokenizers\WhitespaceTokenizer;
use NlpTools\Models\FeatureBasedNB;
use NlpTools\Documents\TrainingSet;
use NlpTools\Documents\TokensDocument;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Classifiers\MultinomialNBClassifier;
 
// ---------- Data ----------------
// data is taken from http://archive.ics.uci.edu/ml/datasets/SMS+Spam+Collection
// we use a part for training
$training = array(
    array('c','Chinese Beijing China'),
    array('c','Chinese Chinese Shanghai'),
    array('c','Chinese Macao'),
    array('j','Tokyo Japan')
);
// and another for evaluating
$testing = array(
    array('c','Chinese	Chinese	Chinese	Tokyo Japan'),
    array('c','India China Chinese'),
    array('c','Japan '),
    array('c','Tokyo')
 );
$tset = new TrainingSet(); // will hold the training documents
$tok = new WhitespaceTokenizer(); // will split into tokens
$ff = new DataAsFeatures(); // see features in documentation
 
// ---------- Training ----------------
foreach ($training as $d)
{
    $tset->addDocument(
        $d[0], // class
        new TokensDocument(
            $tok->tokenize($d[1]) // The actual document
        )
    );
}
 
$model = new FeatureBasedNB(); // train a Naive Bayes model
$model->train($ff,$tset);
 $china = array();
 $ccount1 = 0;
 $jcount = 0;
 $japan = array();
  
// ---------- Classification ----------------
$cls = new MultinomialNBClassifier($ff,$model);
$correct = 0;
foreach ($testing as $d)
{
    // predict if it is spam or ham
    $prediction = $cls->classify(
        array('c','j'), // all possible classes
        new TokensDocument(
            $tok->tokenize($d[1]) // The document
        )
    );
    if ($prediction=='c')
        {
        	$china[$ccount1]=$d[1];
        	$ccount1++;
        }
        else
        {
        	$japan[$jcount]=$d[1];
        	$jcount++;
        }
}
 
printf("Accuracy: %.2f\n", 100*$correct / count($testing));
print($prediction);
echo "<br>";
echo "In class c <br>";

$arrlength=count($china);
 for($x = 0; $x < $arrlength; $x++) {
    echo $china[$x];
    echo "<br>";
}
$arrlength1=count($japan);
echo "<br> In class j <br>";
for($x = 0; $x < $arrlength1; $x++) {
    echo $japan[$x];
    echo "<br>";
}



?>