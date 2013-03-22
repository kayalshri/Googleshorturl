<?php
/*
	@author		:	Giriraj Namachivayam
	@date 		:	Mar 20, 2013
	@demourl	:	http://ngiriraj.com/socialMedia/shorturl/
	@document	:	http://ngiriraj.com/work/
	@license	: 	Free to use
	@History	:	V1.0 - Released oauth 2.0 service providers login access
	@ Reference	:	https://developers.google.com/url-shortener/v1/getting_started#shorten
*/


if ($_GET['shortUrl']){
	$shortUrl = $_GET['shortUrl'];
}else if ($_GET['longUrl']){
	// Short URL Create, (If its already created return Short URL)
	$longUrl = $_GET['longUrl'];
	
	# Get API key from : http://code.google.com/apis/console/
	# Step-by-step tutorial : http://ngiriraj.com/work/google-connect-by-using-oauth-in-php/
	$apiKey = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	
	# Post Data
	$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
	$shortUrlInfo = loadUrl('https://www.googleapis.com/urlshortener/v1/url','POST',$postData);
	
	# Get Short URL
	$shortUrl = $shortUrlInfo['id'];
}else{
	# Default short URL
	$shortUrl="http://goo.gl/Fgdzr";
}


# Short URL Analytics
$analyticsUrl="https://www.googleapis.com/urlshortener/v1/url?shortUrl=".$shortUrl."&projection=FULL";
$analyticsUrlInfo = loadUrl($analyticsUrl,'GET','',0);

# Get the values
$longUrl = $analyticsUrlInfo['longUrl'];

# Default Period is "allTime"
$period = ($_GET['period'])  ?  ($_GET['period'])  : "allTime";
$traffic = $analyticsUrlInfo['analytics'][$period];

# Clicks count
$clicks = $traffic['shortUrlClicks'];

# Get Referrers metrics and build the data
$referrers = $traffic['referrers'];
$ref_draw="['Referrers', 'Count'],";
for ($i=0;$i<sizeOf($referrers);$i++){
	$count = $referrers[$i]['count'];
	$id = $referrers[$i]['id'];
        $ref_draw .=" ['".$id."',".$count."],";
}
$ref_draw = rtrim($ref_draw,',');

# Get Browsers metrics and build the data
$browsers= $traffic['browsers'];
$brow_draw="['browsers', 'Count'],";
for ($i=0;$i<sizeOf($browsers);$i++){
	$count = $browsers[$i]['count'];
	$id = $browsers[$i]['id'];
        $brow_draw .=" ['".$id."',".$count."],";
}
$brow_draw = rtrim($brow_draw,',');

# Get Platform metrics and build the data
$platforms = $traffic['platforms'];
$platforms_draw="['platforms', 'Count'],";
for ($i=0;$i<sizeOf($platforms);$i++){
	$count = $platforms[$i]['count'];
	$id = $platforms[$i]['id'];
        $platforms_draw .=" ['".$id."',".$count."],";
}
$platforms_draw = rtrim($platforms_draw,',');

# Get countries metrics and build the data
$countries= $traffic['countries'];
$countries_draw="['countries', 'Count'],";
for ($i=0;$i<sizeOf($platforms);$i++){
	$count = $countries[$i]['count'];
	$id = $countries[$i]['id'];
        $countries_draw .=" ['".$id."',".$count."],";
}
$countries_draw = rtrim($countries_draw,',');


/*
	function Name 	:	loadUrl
	Params			:	$url	- URL (require)
						$method	- GET, POST (require)
						$postval- POST VALUES in array format (require)
						$debug	- 0-OFF,1-ON [Default:0] (optional)
	return			:	Array Value
*/
function loadUrl($url,$method,$postval,$debug=0){
	$postval = json_encode($postval);
	$curlObj = curl_init();
	curl_setopt($curlObj, CURLOPT_URL,$url );
	curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curlObj, CURLOPT_HEADER, 0);
	curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
	if ($method == 'POST'){
		curl_setopt($curlObj, CURLOPT_POST, 1);
		curl_setopt($curlObj, CURLOPT_POSTFIELDS, $postval);
	}
	$response = json_decode(curl_exec($curlObj),true);
	curl_close($curlObj);
	if($debug){
		print "<pre>";
		print_r($response);
		print "</pre>";
	}
	return $response;
}

?>


<!doctype html> 
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!-- STYLE CSS -->

<link rel="stylesheet" href="style.css">

<!-- GOOGLE CHART -->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
	  google.load('visualization', '1', {'packages': ['geochart']});
     
	// Invoke country,browser,referrer,platform functions
      google.setOnLoadCallback(countryDrawChart);
	  google.setOnLoadCallback(browserDrawChart);
	  google.setOnLoadCallback(refererDrawChart);
	  google.setOnLoadCallback(platformDrawChart);
      
	  // COUNTRY
	  function countryDrawChart() {
                var data = google.visualization.arrayToDataTable([
          <?php echo $countries_draw; ?>
        
          ]);

        var options = {};
        var chart = new google.visualization.GeoChart(document.getElementById('country_div'));
        chart.draw(data, options);
      }
	  
	  // BROWSER
	  function browserDrawChart() {
        var data = google.visualization.arrayToDataTable([
          <?php echo $brow_draw; ?>
         ]);

        var options = {
          title: 'Browser list'
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('browser_div'));
        chart.draw(data, options);
      }
	  
	  // REFERRER
	  function refererDrawChart() {
        var data = google.visualization.arrayToDataTable([
          <?php echo $ref_draw; ?>
        ]);

        var options = {
          title: 'Referrers list'
        };

        var chart = new google.visualization.LineChart(document.getElementById('referer_div'));
        chart.draw(data, options);
      }
	  
	  // PLATFORM
	  function platformDrawChart() {
        var data = google.visualization.arrayToDataTable([
          <?php echo $platforms_draw; ?>
        ]);

        var options = {
          title: 'Platform list'
        };

        var chart = new google.visualization.LineChart(document.getElementById('platform_div'));
        chart.draw(data, options);
      }

    </script>

</head>
<body>

<div id="menu-primary">Google ShortUrl Analytics</div>
<Br><BR><BR><BR><BR>
<div id="main">
	<h1 class="we-are">
	<span>Demo</span>
	</h1>
	<BR>

	<center>
		<form method='GET' action="">
			Period 
				<input type='radio' name='period' id="period" value='allTime' checked > All Time
				<input type='radio' name='period' id="period" value='month'> Month
				<input type='radio' name='period' id="period" value='week'> Week
				<input type='radio' name='period' id="period" value='day'> day
				<input type='radio' name='period' id="period" value='twoHours'> TwoHours
				<br>
			Long URL : 
			<input type="text" id="longUrl" name="longUrl" class="tbox" value="<?php echo $longUrl; ?>"> 
			<input type="submit" value="Create Google ShortUrl" class="sbutton">
			<BR>
			[or]
			<BR>
			Short URL : 
			<input type="text" id="shortUrl" name="shortUrl" class="tbox" value=""> 
			<input type="submit" value="Get Traffic Report" class="sbutton">
		</form>
		
		<h1>Total clicks <?php echo $clicks; ?> - <a href="<?php echo $shortUrl;?>" target="_blank"><?php echo $shortUrl;?></a>		 </h1>

		<div class="wide "><div id="country_div" style="width: 100%; height: 100%;"></div></div>
		<div class="wide "><div id="browser_div" style="width: 100%; height: 100%;"></div></div>
		<div class="wide "><div id="referer_div" style="width: 100%; height: 100%;"></div></div>
		<div class="wide "><div id="platform_div" style="width: 100%; height: 100%;"></div></div>

	</Center>

</div>

</body>
</html>