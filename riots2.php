<?php
 /* Küsib db andmed vastavalt sissetulevatele muutstele ning xml-na tagastab need
 */
 
include "dbconnect.php"; 
   
// et db  oleks lihstam küsida, siis kuupäav õiget pidi
function dateFormat($date){
       
       $date_split = explode('.',$date);
       $d = $date_split[0];
       $m = $date_split[1];
       $y =  $date_split[2];
       $date_string = "$y-$m-$d";
       $date= date("Y-m-d", strtotime($date_string));
       return $date;
     }
//muutujad     
      $to = $_GET['t'];
      $from = $_GET['f'];  
      
       $to;
       $from;
  // kui on tegemist kuupaeva vahtetuse või vahemikus vahetusega
      if ($to!=""){ 
            $to = dateFormat($to);
          //vahemiku vahetus
           if ($from!=""){
           
               $from = dateFormat($from);
               $query = 'SELECT Marker.*,Feed.*,Country.CountryID, Country.Lat, Country.Lng, Article.*,DATE_FORMAT(Article.PubDate, "%d.%m.%y %H:%m")as PubDate FROM Marker, Country, Article, Feed WHERE Marker.CountryID=Country.CountryID AND Marker.ArticleID=Article.ID AND Feed.FeedID=Article.FeedID AND  DATE(Article.PubDate)>= "'.$from.'" AND DATE(Article.PubDate) <= "'.$to.'"';
           
           } else {
          // kuupäeva vahetus 
                 $query = 'SELECT Marker.*,Feed.*,Country.CountryID, Country.Lat, Country.Lng, Article.*,DATE_FORMAT(Article.PubDate, "%d.%m.%y %H:%m")as PubDate FROM Marker, Country, Article, Feed WHERE Marker.CountryID=Country.CountryID AND Marker.ArticleID=Article.ID AND Feed.FeedID=Article.FeedID AND DATE(Article.PubDate) = "'.$to.'"';
           }   
                
      } else{
         // yldine
           
           $query = 'SELECT Marker.*,Feed.*,Country.CountryID, Country.Lat, Country.Lng, Article.*,DATE_FORMAT(Article.PubDate, "%d.%m.%y %H:%m")as PubDate FROM Marker, Country, Article, Feed WHERE Marker.CountryID=Country.CountryID AND Marker.ArticleID=Article.ID AND Feed.FeedID=Article.FeedID';
        }
     
            
$result = mysql_query($query);
if (!$result) {  
  die('Invalid query: ' . mysql_error());
} 

// xml- loomine
 $dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

header("Content-type: text/xml"); 

// Iterate through the rows, adding XML nodes for each
  
while ($row = @mysql_fetch_assoc($result)){ 
 
   $enc =  utf8_encode($row['Title']); 
  // ADD TO XML DOCUMENT NODE  
  $node = $dom->createElement("marker");  
  $newnode = $parnode->appendChild($node);
  $newnode->setAttribute("lat", $row['Lat']);    //koordinaat
  $newnode->setAttribute("lng", $row['Lng']);    
  $newnode->setAttribute("Title", $enc);          //artikli pealkiri
  $newnode->setAttribute("Link", $row['ArticleHref']);  // artikli link
  $newnode->setAttribute("PubDate", $row['PubDate']);  //avaltud
  $newnode->setAttribute("FeedName", $row['FeedName']); //feedi nimi
  $newnode->setAttribute("FeedID", $row['FeedID']);   //feedi id
  $newnode->setAttribute("FeedUrl", $row['FeedUrl']); //feedi url
   
} 


 
echo $dom->saveXML();
 
  
?>
   
