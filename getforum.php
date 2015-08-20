<?php

    ob_start();
    $target_url = $_GET['q'];
    $qi=$_GET['qi'];
    $present=0;
	$question_id=0;
 	include('autoloader.php');
    use NlpTools\Classifiers\MultinomialNBClassifier;
    use NlpTools\Documents\TokensDocument;
    use NlpTools\Documents\TrainingSet;
    use NlpTools\FeatureFactories\DataAsFeatures;
    use NlpTools\Models\FeatureBasedNB;
    use NlpTools\Stemmers\PorterStemmer;
    use NlpTools\Tokenizers\WhitespaceTokenizer;
    use NlpTools\Utils\StopWords;
    include_once('simple_html_dom.php');

  	$html = new simple_html_dom();
  	$html->load_file($target_url);
	
	include_once('connect.inc.php');
	//echo "<br/>";
	

	foreach($html->find('h1') as $link)
	{	 
		$ans=$link->getAttribute('itemprop');
		if($ans=="name")
		{
			foreach($link->find('a') as $link1)
			{
				echo "<div class='col-lg-offset-1 bg-primary col-lg-10' style='border-bottom:1px solid;'>";
				echo '<h1>'.$link1->text()."</h1><br/>";
				
				echo "</div>";	
				
				$query="SELECT * from `question` where quest='".$link1->text()."'";
				$query_run=mysql_query($query);
				if(mysql_num_rows($query_run)==1)
				{
					$present=1;
					while($row=mysql_fetch_array($query_run))
					{
						$question_id=$row['q_id'];
					}
				}
				else
				{
					$query="INSERT into `question` values (".$qi.",'".$link1->text()."')";
					if($query_run=mysql_query($query))
					{
						echo 'inserted';
					}
					$question_id=$qi;
				}
				
				
			}
		}
	}//question is checked whether it is present or not
	$flag=0;
	if($present==1)//if it is present in database retreive from database
	{
		
		foreach($html->find('div') as $link)
		{
		 	//echo $link."<br/>";
			$ans=$link->getAttribute('class');
			if($ans=="post-text" && $flag==0)
			{
				$flag=1;
				echo "<div class='col-lg-offset-1 bg-primary col-lg-10' style='border-bottom:1px solid;padding-bottom:10px;'>";
					echo "Explanation <br/>";
					echo "<p>".$link."</p>";
					
					echo "</div>";	
			}
		}

		//echo "Answers:";
		$query="SELECT * from `response` where `q_id`=$question_id ORDER BY classify DESC";
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
			echo "<button type='button' id='".$row['r_id']."' onclick='stop(this);' class='col-lg-offset-1 col-sm-offset-1 col-md-offset-1 btn btn-info'>Stop Words</button>";
			
			echo "</div>";

		}
	
	?>
	<div class="container">
	<form method="POST" action="getclassify.php">
	  <div class="form-group col-lg-offset-3 col-lg-6">
	    <label for="post11">Reply to the post:</label>
	    <input type="text" class="form-control" id="post1" name="post1" placeholder="Enter your response">
	    <input type="text" class="form-control" id="q" name="q" style="display:none;" value="<?php echo $question_id;?>">
	  </div>
	  <div class="form-group col-lg-offset-3 col-lg-6">
	  <button type="submit" class="btn btn-default">Submit</button>
	</div>
	</form>
	</div>
	<?php
	
	}
	else //if it is not present in database den insert into database
	{
		echo "Nandish Answers:<br/><br/>";
		$Allresponse=array();
		$flag=0;
		foreach($html->find('td') as $link)
		{
			 //echo $link."<br/>"; 
			$ans=$link->getAttribute('class');
			if($ans=="answercell")
			{
				foreach($link->find('div') as $res)
				{
					$resclass=$res->getAttribute('class');
					if($resclass=="post-text")
					{
						$store=$res->text();
						$store=str_replace('"','',$store);
						$store=str_replace("'",'',$store);

						$disp=$res;
						$disp=str_replace('"','###',$disp);		//###  - "
						$disp=str_replace("'","&&&",$disp);		//&&&  - '

						//echo $store."<br/>";
						echo $res."<br/>";

						$query="INSERT into `response` values ('',".$question_id.",'".$store."','".$disp."',1)";
						if($query_run=mysql_query($query))
						{
							echo '<h1>inserted</h1>';
						}
						else
						{
							echo '<h1>Not inserted</h1>';
						}
						
						//array_push($Allresponse,$res);
					}
				}
				
			}
			$flag=1;
		}// for each close


	}//present wala else close

	if($present==0)
	{
	
		$training=array();
		$query="SELECT * from `training` where q_id=$question_id";
	    $query_run=mysql_query($query);
	    
	    while($row=mysql_fetch_array($query_run))
	    {
	        $single_resp=$row['resp'];
	        $class=$row['class'];
	        array_push($training, array($class,$single_resp));        
	    }


		$testing=array();
		$query="SELECT * from `response` where q_id=$question_id";
	    $query_run=mysql_query($query);
	    
	    while($row=mysql_fetch_array($query_run))
	    {
	        $single_resp=$row['resp'];
	        array_push($testing, array('ans',$single_resp));        
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
		$correct = 0;
		foreach ($testing as $d)
		{
		    // predict if it is spam or ham
		    echo $prediction = $cls->classify(
		        array('ans','non'), // all possible classes
		        new TokensDocument(
		             $tok->tokenize($d[1]) // The document
		        )
		    );

		    $puts=0;
		    if ($prediction==$d[0])
		    { 
		    	echo $correct ++;
		        $puts=1;   
		    }
		    $query="UPDATE `response` SET classify=$puts where resp='".$d[1]."'";
		    if($query_run=mysql_query($query))
		    {
		        echo 'updated';
		    }
		    echo "<br/>";
		}
	}// 'if' classification closed
?>