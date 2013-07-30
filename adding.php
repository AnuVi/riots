<?php
  include "dbconnect.php";
 
/* Andmete lisamine andmebaasi
   muutujad: feed / feedi nimed
   funktsioonid: järgmise id teada saamine-   nextID
                 ajatsiooni muutmine  -  timeZoneEst
                 andmete lisamine andebaast - getFeed
      
*/   
  
 // feeds
    $feed_list = array("http://rss.nytimes.com/services/xml/rss/nyt/World.xml","http://www.postimees.ee/rss/", "http://www.thetimes.co.uk/tto/news/world/rss");
    
    // ilusad feedi nimed
    $name_list = array("New York Times","Postimees" ,"The Times");
    
    //nii palju kui on feed_listis liikmeid kutsu esile funktsioon getFeed
    $feeds_count = count($feed_list);
    
    //hetke aeg 
    $now = date('Y-m-d H:m:s'); 
    
    
    // järgmise id teada saamine
  
   function nextID( $field=0, $table=0 )     
	        {
	              include "dbconnect.php";    
	              $query=mysql_query('SELECT max('.$field.') as nextid from '.$table.'');

	                 if( $query)  
	                  {      $row = mysql_fetch_array( $query );
	                          return $row['nextid'] + 1;       
	                  }
	        }   
	        

//  aegade kuvamine eesti aja järgi
   function timeZoneEst($t){
           $tz = new DateTimeZone('Europe/Tallinn');
           $tz_t = new DateTime($t);
           $tz_t -> setTimezone($tz);
           $tz_t =  $tz_t ->format("Y-m-d H:i:s");
           return $tz_t;
     } 
    // lisamise aeg db
    $added_date = timeZoneEst($now);
    
    // andmete lisamine anmdebaasi
   function getFeed($feed_url,$source_name,  $added_latest, $added_date){
            echo "jah";
 
      //esialgne kontroll,kui unustad panna ilusa feedi nime
      if($source_name == Null){
        $source_name = "Allikas tuvastamata";
      } 
      
      //et saaksime db Articles siduda/lisada db Feed-iga, siis hetkel selline lahendus
      
     switch ($source_name){
          case "New York Times":
             $feed_id = 1;
             $lang = "eng";
             break;
             
          case "Postimees":
             $feed_id = 2;
             $lang = "est";
             break;
          
          case "The Times":
             $feed_id = 3;
             $lang = "eng";
             break;
             
          default :
             $feed_id = 0;
             break;
     
     }
  //
       
  // feedide sisse lugmemine        
      $content = file_get_contents($feed_url); 
      $channel = new SimpleXmlElement($content); 
        
        foreach($channel->channel->item as $item) {  
          
         $title = $item->title;    //pealkiri
        $pub_date = $item->pubDate;   //avaldamise aeg
         $link =  $item->link;
          $cat =  $item->category;
         //echo var_dump($item);
         
        // avaldamise aeg Eesti aja järgi 
        $tz = new DateTimeZone('Europe/Tallinn');
        $date = new DateTime($pub_date, new DateTimeZone('GMT'));
       
        $date -> setTimezone($tz);   
        //echo var_dump($date->format(DATE_RFC822));
        $pub_date = $date ->format("Y-m-d H:i:s");
        
        //kui on uusi ajaliselt uuemad artikleid kui andmebaasi lisatud  
        if($added_latest<$pub_date){
        
        // artikli sidumiseks riigi riigiga                        
           $q_country =mysql_query ('SELECT CountryID, CNameEng, CNameEst FROM  Country');
    
          //kontrollime, et pealkirjas oleks soovitud sõnad
          
           if($lang =="est"){
                $needed_words = array('/ülestõus/','/demonstratsioon/','/revolutsioon/','/protestijad/','/rahutused/','/protest/');
           
           } else {
                $needed_words = array('/riot/','/revolution/','/protest/','/protests/','demonstration');
           
           } 
          
                
            foreach( $needed_words  as $word){
                     // artiklis sõnad väiksete tähtedega
                      
                     $title_lower = strtolower($title);
                     if( preg_match( $word , $title_lower )){
                               echo $title.$link.$cat.'</br>';
                               echo "jah";
                    // siin peaks olema  riigi tuvastamine
                         
                  // andmebaasi lisamine
                   $article_ID = nextID('ID','Article');
                   
                   //lisab artikli db Article
                   $insert_article = mysql_query('INSERT INTO Article 
                      VALUES('.$article_ID.', '.$feed_id.',"'.$title.'", "'.$pub_date.'", "'.$added_date.'", "'.$link.'") ');  
                      
                   // lisab markeri, artikli, riigi db Marker   
                 /*   $insert_marker = INSERT INTO  Marker VALUES ('.nextID('MarkerID','Marker').', '.$CountrID.', "'.$article_ID.'") ';*/ 
                 
                     
                    }  
                
          } // word match  
        } //if added_latest end   
        }//foreach channel end 
          
        
    }//getFeed end
    
// nii kaua liikmeid feedi massiivis kutsume funktsiooni esile

for ($i=0; $i<=$feeds_count; $i++){
          getFeed($feed_list[$i],$name_list[$i], $added_latest,$added_date);

    }
    
    


     
 
    
  
    

    
   
    
        
    
?>



