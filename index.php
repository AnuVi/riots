<?php
   include "dbconnect.php";  
   mysql_set_charset('utf8');
    
  
    
//vaikimisi ajavahemku näitamine,
    $q_dt_rng = mysql_query('SELECT MIN(DATE_FORMAT(PubDate, "%d.%m.%Y ")), MAX(DATE_FORMAT(PubDate, "%d.%m.%Y "))  FROM Article');
         $row = mysql_fetch_array($q_dt_rng);
            
        $default_date = $row[0].' - '.$row['1'];

?>
<!DOCTYPE html>
<html lang="et" dir="ltr">
<head>
	<meta charset="UTF-8">
	<title>Protestid maailmas</title>
	<!--syles-->
	 <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	 <link rel="stylesheet" href="style.css" />
	
	<!--scripts-->
	<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyC0YzYmC9hFjW_7OGQ6xRFcnf2se1apZTI&sensor=false&v=3&libraries=geometry">
  </script>
  <script  src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script  src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>  
  <script type="text/javascript">   
      //<![CDATA[
    // ikoonid vastavalt feedile
    var customIcons = {
      1: {
        icon: 'http://maps.google.com/mapfiles/marker_blackN.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
      },
      2: {
        icon: 'http://maps.google.com/mapfiles/marker_purpleP.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
      },
      3: {
        icon: 'http://maps.google.com/mapfiles/marker_greyT.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
      }
    };
      function load() {
     //map
      var map = new google.maps.Map(document.getElementById("map"), {
        center: new google.maps.LatLng(25.8254, -33.2031),
        zoom: 2,
        mapTypeId: 'roadmap'
      });
      // muutujad
         var infoWindow = new google.maps.InfoWindow();
         var min = .99;
         var max = 1.000001;
         var title,
              type,
              link,
              pDate,
              feedTitle,
              dat,
              feedLink;
       
       //markerid
      // sellest failist kuvatakse  
      downloadUrl('riots2.php', function(data) {
        var xml = data.responseXML;
        var markers= xml.documentElement.getElementsByTagName("marker");  
          
        for (var i = 0; i <markers.length; i++) {
     
        title = markers[i].getAttribute("Title");
        pDate = markers[i].getAttribute("PubDate");
        type = markers[i].getAttribute("FeedID");
        feedName = markers[i].getAttribute("FeedName");
        aLink =  markers[i].getAttribute("Link");
        fLink = markers[i].getAttribute("FeedUrl"); 
        // kuna ühe koordinaadiga on seotud mitu uudist, siis väike laialipilidumne
          var offsetLat = markers[i].getAttribute("lat") * (Math.random() * (max - min) + min);
          var offsetLng = markers[i].getAttribute("lng") * (Math.random() * (max - min) + min);
        // infoWindow info
          var info = '<p><a href="'+ aLink +'" target="_blank" title="'+title+'"><b>' + title + '</b></a></p>'+
                     '<p><a href="'+ fLink +'" target="_blank" title="'+feedName+'">' + feedName + '</a>: '+
                      '<span>' + pDate + '</span></p>';
                               
          var point = new google.maps.LatLng(
               offsetLat, offsetLng
              
              );
          
          var icon = customIcons[type] || {};
          var marker = new google.maps.Marker({
            map: map,
            position: point,
            icon: icon.icon,
            shadow: icon.shadow,
          
          });
         // kui klikitakse markeri peal 
          google.maps.event.addListener(marker, 'click', (function(marker,info)       {
                          
                        return function() {
                               
                                map.setZoom(9); 
                                map.setCenter(marker.getPosition());
                                infoWindow.setContent(info);
                               infoWindow.open(map,marker);
                                
                            
                        }
                    })(marker, info)); 
         
        }   
       
      });
     
   }
    // anmdevahetus
    
    function downloadUrl(url, callback) {
      var request = window.ActiveXObject ?
          new ActiveXObject('Microsoft.XMLHTTP') :
          new XMLHttpRequest;
      // uus info
      var f= '<?php echo $_POST['from']?>';
       var t= '<?php echo $_POST['to']?>';
      var params ='f='+f+'&t='+t  ;
      
      // kuupäeva kuvamine pealkirja all 
      var u = document.getElementById("w-dt");
   
      if (f==""){
          u.innerHTML=t;
         
      } else{
          u.innerHTML=f+' - '+t;
      
      } 
      
      if(!f && !t){
          u.innerHTML='<?=$default_date?>';;
      }
      
      request.onreadystatechange = function() {
        if (request.readyState == 4) {  
        
          request.onreadystatechange = doNothing;
          callback(request, request.status);
            
        }
      };
     
      request.open('GET', url+'?'+params, true);
      request.send(params);
    }

    function doNothing() {}
    
    //]]>
  </script>
	<script>
   
  $(function() {
  
  // see toredus on kuupaeväljade ja nuppude nätamine
  // hetkel liigseiv muutujaid
   var frmFld = $("#frm-fld"),
       tFld =  $("#t-fld"),
       from = $("#from"),
       to = $("#to"), 
      dtChng = $("#dt-chng"),
      rngChng = $("#rng-chng"),
      sbmt = $("#sbmt"),
      rst = $("#rst"),
      fromLabel = from.prev('label'),
      toLabel = to.prev('label');
     
  
   //peidame, kuniks ei klikita soovitud kuupäeva vahetusel  
     frmFld.hide();
     tFld.hide();
     sbmt.hide();
     rst.hide();   
    
    

   // kui soovitakse muuta ainult ühte kuupäeva 
     dtChng.click(function(){
           //näidatakse
          tFld.show();
          to.focus();
          sbmt.show();
          rst.show();  
          //peidetakse
          toLabel.hide();
          dtChng.hide();
          rngChng.hide();  
          
    
     });
  // kui soovitakse muuta vahemikku
        rngChng.click(function(){
         
          //näidatakse
          frmFld.show();
          from.focus();
          sbmt.show();
          rst.show();   
          
          // peidetakse
      
          dtChng.hide(); 
          rngChng.hide(); 
          
          
    
     });  
     
// kui loobutakse
     
        rst.on('click', function(){
          //peidetakse
         sbmt.hide();
          rst.hide();
          frmFld.hide();
          tFld.hide();  
          //näidatakse
          dtChng.show();
          rngChng.show();
          // imet teeb see asi - vähemalt hetkeks tundus
           $("input[type='text']").datepicker( "setDate" , null );
   
     });
     
  
// datepickeri seadistused  vastavalt hetkel reaalselt db olevatele andmetele
     from.datepicker({
      defaultDate: "-1w",
      dateFormat: 'dd.mm.yy',
      changeMonth: true,
      numberOfMonths: 1,
      minDate: new Date(2013, 07-1, 24),
      maxDate:new Date(2013, 07-1, 30),  //hetkel kuna db on selle kuu päevaga viimane  
      onClose: function( selectedDate ) {
        
         tFld.show();
          tFld.show();
          toLabel.show();
          to.datepicker( "option", "minDate", selectedDate );
      } 
    });
    to.datepicker({
      defaultdate: new Date(2013, 07-1, 30),
      dateFormat: 'dd.mm.yy', 
      minDate: new Date(2013, 07-1, 24),
      maxDate:new Date(2013, 07-1, 30),
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
           
         
          from.datepicker( "option", "maxDate", selectedDate );
      }
    }); 
    
   
      
  });     
  </script>
	
</head>
<body onload="load()">
  <div class="wrap" role="main">     
  	 <h1>Protestid maailmas:</h1>
  	 <p id="w-dt" class="w-d">&nbsp;</p>
  	 <form id="dt-frm" method="post" action="index.php" role="form">
  	    <fieldset id="frm-fld">
  			 <label for="from">Alates:</label>
  			 <input type="text" id="from" name="from" readonly />
  		  </fieldset>
  		  <fieldset id="t-fld">
  			 <label for="to">Kuni:</label>
  			 <input type="text" id="to" name="to" readonly/>
  		  </fieldset>
  		  <fieldset>
          <button id="dt-chng"  type="button" class="bt-v1" role="button"> Vali uus kuupäev</button>  
          <button id="rng-chng" type="button" class="bt-v1" role="button"> Vali kuupäeva vahemik</button>  
      </fieldset>
  		<fieldset>
  			<input id="sbmt" type="submit" value="Valitud!">
  			<button id="rst" class="bt-v2" type="button" role="button"> Loobu</button>
  		</fieldset>
  	</form>    <!--form end -->
  
  		<div id="map" style="width: 1000px; height: 400px"></div>
  
  	<p class="legend">
      <span><img src="http://maps.google.com/mapfiles/marker_blackN.png" alt="ikoon">New York Times</span>
      <span><img src="http://maps.google.com/mapfiles/marker_purpleP.png" alt="ikoon"/>Postimees</span><span><img src="http://maps.google.com/mapfiles/marker_greyT.png" alt="ikoon"/>The Times</span>
    </p>  <!-- legend-->
  </div>  <!-- wrap-->
  
	
</body>

</html>