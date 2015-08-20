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
    
    $q = $_GET['q'];
    ///////////////////////////
    include_once('connect.inc.php');
	$training=array();
	$query="SELECT * from `training` where q_id=$q";
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

	$testing=array();
	$query="SELECT * from `response` where q_id=$q";
    $query_run=mysql_query($query);
    
    while($row=mysql_fetch_array($query_run))
    {
        $single_resp=$row['resp'];
        array_push($testing, array('ans',$single_resp));        
    }
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
    $query="UPDATE `response` SET classify=$puts where resp='".mysql_real_escape_string($d[1])."'";
    if($query_run=mysql_query($query))
    {
      
    }
    
	}
	echo "<div class='col-lg-10 bg-primary col-lg-offset-1' style='border-bottom:1px solid;'>";
    $query="SELECT * from `question` where `q_id`=$q";
    $query_run=mysql_query($query);
    while($row=mysql_fetch_array($query_run))
    {
        $quest=$row['quest'];
        echo "<h1>".$quest."</h1>";
    }
	
	echo "</div>";

	$query="SELECT * from `response` where `q_id`=$q ORDER BY classify DESC";
	$query_run=mysql_query($query);
	while($row=mysql_fetch_array($query_run))
	{
		$classify=$row['classify'];
		$ret=$row['resp_disp'];
        $ret=str_replace('###', '"', $ret);
        $ret=str_replace("&&&", "'", $ret);
		if($classify==1)
		{
			echo "<div class='col-lg-offset-1 bg-success col-lg-10' style='border-bottom:1px solid;padding-bottom:10px;'>";
			echo "<p>".$ret."</p>";
			
				
		}
		else
		{
			echo "<div class='col-lg-offset-1 col-lg-10 bg-warning' style='border-bottom:1px solid;padding-bottom:10px;'>";
			echo "<p>".$ret."</p>";
			
			
		}
		echo "<button type='button' id='".$row['r_id']."'  onclick='stem(this);' class='btn btn-info'>Stemmer</button>";
		echo "<button type='button' id='".$row['r_id']."' onclick='stop(this);' class='col-lg-offset-1 btn btn-info'>Stop Words</button>";
		
		echo "</div>";
	}
?>
    <div class="container">
    <form method="POST" action="getclassify.php">
    <div class="form-group col-lg-offset-3 col-lg-6">
        <label for="post11">Reply to the post:</label>
        <input type="text" class="form-control" id="post1" name="post1" placeholder="Enter your response">
        <input type="text" class="form-control" id="q" name="q" style="display:none;" value="<?php echo $q;?>">
      </div>
      <div class="form-group col-lg-offset-3 col-lg-6">
      <button type="submit" class="btn btn-default">Submit</button>
    </div>
    </form>
    </div>