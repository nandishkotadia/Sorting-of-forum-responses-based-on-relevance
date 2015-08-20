<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
</head>
<body style="background-color:#63e8b3">
    <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <?php
    //stop words
   ob_start();
  ?>
  <div class="col-lg-12" style="height:40px;background-color:#222222;">

  </div>

  <!--header UPDATE `response` SET q_id=206 WHERE q_id=104;-->
  <div class="col-lg-12" style="padding-top:2%;">
    <div class="col-lg-2">
        <!--stack overflow-->
        <div class="list-group">
          <a href="#" class="list-group-item active">
             StackOverflow
          </a>
<!--           <a href="#" onclick="showForum('http://stackoverflow.com/questions/12374399/difference-between-method-overloading-and-overriding-in-java','201')" class="list-group-item">Method overloading & overriding</a> -->
          <a href="#" onclick="showForum('http://stackoverflow.com/questions/513832/how-do-i-compare-strings-in-java','202')" class="list-group-item">Compare strings in java</a>
          <a href="#" onclick="showForum('http://stackoverflow.com/questions/767372/java-string-equals-versus','203')" class="list-group-item">java string versus ==</a>
          <a href="#" onclick="showForum('http://stackoverflow.com/questions/2941900/is-it-wrong-to-use-deprecated-methods-or-classes-in-java','204')" class="list-group-item">Is it wrong to use Deprecated methods or classes in Java?</a>
          <!--<a href="#" onclick="showForum('http://stackoverflow.com/questions/215497/in-java-whats-the-difference-between-public-default-protected-and-private','205')" class="list-group-item">In Java, what's the difference between public, default, protected, and private?</a>
          --><a href="#" onclick="showForum('http://stackoverflow.com/questions/92859/what-are-the-differences-between-struct-and-class-in-c','206')" class="list-group-item">What are the differences between struct and class in C++?</a>
          <a href="#" onclick="showForum('http://stackoverflow.com/questions/2439243/what-is-the-difference-between-string-and-stringbuffer-in-java','207')" class="list-group-item">Difference between String and StringBuffer in java ?</a>
        </div>
        <!--database -->
        <div class="list-group">
          <a href="#" class="list-group-item active">
             Yahoo Answers
          </a>
          <!--yahoo database-->
          <a href="#" onclick="showDb('103')" class="list-group-item">I lost my printer CD Hp Deskjet 1050 J410 i can not install printer to my computer i need it badly.?</a>
          <a href="#" onclick="showDb('102')" class="list-group-item">What are CSS, JavaScript and HTML in web design?</a>
          <a href="#" onclick="showDb('101')" class="list-group-item">What are the differences between a HashMap and a Hashtable in Java?</a>

          <a href="#" onclick="showDb('104')" class="list-group-item">What is a plugin?</a>
          <a href="#" onclick="showDb('105')" class="list-group-item">What is linux and unix?</a>
          <a href="#" onclick="showDb('106')" class="list-group-item">What is the use of java?</a>
          <a href="#" onclick="showDb('107')" class="list-group-item">What is the different between the Xml and Html</a>
          <!--virtual-->
          <a href="#" onclick="showDb('5')" class="list-group-item">Virtual Router</a>
         
        </div>
    </div>
    <div class="col-lg-10 disp-forum" id="disp-forum">
        Welcome

    </div>
  </div>
  <!--main content-->  


  <!--java script-->

  <script type="text/javascript">
    //show forum by fetching
    function showForum(str,str1)
    {
      
      if (str=="") {
        document.getElementById("txtHint").innerHTML="";
        return;
      } 
       
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
      } else { // code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          document.getElementById("disp-forum").innerHTML=xmlhttp.responseText;
        }
      }
      xmlhttp.open("GET","getforum.php?q="+str+"&qi="+str1,true);
      xmlhttp.send();

    }

    //database wala ajax
    function showDb(str)
    {
      
      if (str=="") {
        document.getElementById("txtHint").innerHTML="";
        return;
      } 
       
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
      } else { // code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          document.getElementById("disp-forum").innerHTML=xmlhttp.responseText;
        }
      }
      xmlhttp.open("GET","getdb.php?q="+str,true);
      xmlhttp.send();

    }


    //stemming and stop words
    function stem(el)
    {
        var z=$(el).attr("id");
        
        location.href='details.php?type=stem&id='+z;
        alert("Stemmer"+z);
    }
    function stop(el)
    {
        var z=$(el).attr("id");
        location.href='details.php?type=stop&id='+z;
        alert("Stop Words"+z);
    }
  </script>

</body>
</html>