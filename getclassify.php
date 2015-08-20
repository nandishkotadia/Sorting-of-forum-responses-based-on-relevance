<?php

 	ob_start();
    include('autoloader.php');
    use NlpTools\Classifiers\MultinomialNBClassifier;
    use NlpTools\Documents\TokensDocument;
    use NlpTools\Documents\TrainingSet;
    use NlpTools\FeatureFactories\DataAsFeatures;
    use NlpTools\Models\FeatureBasedNB;
    use NlpTools\Stemmers\PorterStemmer;
    use NlpTools\Tokenizers\WhitespaceTokenizer;
    use NlpTools\Utils\StopWords;
	
	 $put_resp=$_POST['post1'];
	 $put_q=$_POST['q'];

	$mysql_host = 'localhost';
	$mysql_user = 'root';
	$mysql_pass = '';
	$mysql_db = 'be_proj';
	if(!mysql_connect($mysql_host, $mysql_user, $mysql_pass) || !mysql_select_db($mysql_db))
	{
		die(mysql_error());
	}
	mysql_query("SET NAMES 'utf8'");
	

	$training=array();
	$query="SELECT * from `training` where q_id=$put_q";
    $query_run=mysql_query($query);
    
    while($row=mysql_fetch_array($query_run))
    {
        $single_resp=$row['resp'];
        $class=$row['class'];
        array_push($training, array($class,$single_resp));        
    }



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
	 
 
	// ---------- Classification ----------------
	$cls = new MultinomialNBClassifier($ff,$model);
	$testing=array(
		array('ans',$put_resp)
		);
	foreach ($testing as $d)
	{
	    // predict if it is spam or ham
	     $prediction = $cls->classify(
	        array('ans','non'), // all possible classes
	        new TokensDocument(
	             $tok->tokenize($d[1]) // The document
	        )
	    );

	    $puts=0;
	    if ($prediction==$d[0])
	    { 
	    	
	        $puts=1;   
	    }
	    //echo $puts;
	  
	    $query="INSERT into `response` values ('',".$put_q.",'".$put_resp."','".$put_resp."',".$puts.")";
		if($query_run=mysql_query($query))
		{
			//echo 'inserted '.$puts;
		}
	    echo "<br/>";
	    //header('location:stack.php');
	}	
?>
<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script type="text/javascript">alert('<?php echo $prediction." ".$put_resp; ?>');</script>
<?php
	ob_start();		
	echo("<script>location.href ='index.php'</script>");
	ob_end_flush();
 	exit();
?>