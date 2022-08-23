function rgb2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
     ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
     ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) :"" ;
   }

   jQuery(document).ready(function() {
     
     //Make sure image is loaded before running.
     colorChange();
     alert("loaded");
function colorChange(){
     var $myImage = jQuery("#theimage' . $unique . '");
     var colorThief = new ColorThief();
     
     var cp = colorThief.getPalette('.'$myImage[0], 4, 5'.');      
   
   var firstColor = "rgb("+cp[0][0]+","+cp[0][1]+","+cp[0][2]+")";
   var secondColor = "rgb("+cp[1][0]+","+cp[1][1]+","+cp[1][2]+")";
   var thirdColor = "rgb("+cp[2][0]+","+cp[2][1]+","+cp[2][2]+")";
   var fourthColor = "rgb("+cp[3][0]+","+cp[3][1]+","+cp[3][2]+")";

   var firstInHex = rgb2hex(firstColor); 
   var secondInHex = rgb2hex(secondColor); 
   var thirdInHex = rgb2hex(thirdColor); 
   var fourthInHex = rgb2hex(fourthColor); 

   jQuery("#first").val(firstInHex);
   jQuery("#second").val(secondInHex);
   jQuery("#third").val(thirdInHex);
   jQuery("#fourth").val(fourthInHex);

   alert("yeehaw");
   
   
   }
   jQuery("#button").click(function() {
  
   jQuery(this).css("background-color", jQuery("#second").val());
   alert("color 1 is " + jQuery("#first").val() + " color 2 is " + jQuery("#second").val() + " color 3 is " + jQuery("#third").val() + " color 4 is " + jQuery("#fourth").val());

   });
   });