<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>plexWatch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/plexwatch.css" rel="stylesheet">
	
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>

    <!-- touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/icon_iphone.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/icon_ipad.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/icon_iphone@2x.png">
	<link rel="apple-touch-icon" sizes="144x144" href="images/icon_ipad@2x.png">
  </head>

  <body>

  
  
	<div class="container">
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<a href="index.php"><div class="logo"></div></a>
				<ul class="nav">
					
					<li class="active"><a href="index.php"><i class="icon-home icon-white"></i> Home</a></li>
					<li><a href="history.php"><i class="icon-calendar icon-white"></i> History</a></li>
					<li><a href="users.php"><i class="icon-user icon-white"></i> Users</a></li>
					<li><a href="charts.php"><i class="icon-list icon-white"></i> Charts</a></li>
					
				</ul>
				
			</div>
		</div>
    </div>

	
	
	<div class="container-fluid">
		<div class='row-fluid'>
			<div class='span12'>
				
			</div>
		</div>
		<div class='row-fluid'>
			<div class='span12'>
				<?php
			
				$guisettingsFile = "config/config.php";
				if (file_exists($guisettingsFile)) { 
				require_once(dirname(__FILE__) . '/config/config.php');
				
				}else{
					header("Location: settings.php");
				}
				
				if ($plexWatch['https'] == 'yes') {
					$plexWatchPmsUrl = "https://".$plexWatch['pmsIp'].":".$plexWatch['pmsHttpsPort']."";
				}else if ($plexWatch['https'] == 'no') {
					$plexWatchPmsUrl = "http://".$plexWatch['pmsIp'].":".$plexWatch['pmsHttpPort']."";
				}
				
				$statusSessions = simplexml_load_file("".$plexWatchPmsUrl."/status/sessions") or die ("Failed to access Plex Media Server. Please check your server and config.php settings.");

					echo "<div class='wellbg'>";
						echo "<div class='wellheader'>";
						echo "<div class='dashboard-wellheader'>";
							echo "<h3>Plex Status</h3>";
						echo "</div>";
					echo "</div>";
						
					echo "<div class='dashboard-status-wrapper'>";
							// Let's check Plex Media Server ports
							$pmsHttp = fsockopen($plexWatch['pmsIp'], $plexWatch['pmsHttpPort']);
							$pmsHttps = fsockopen($plexWatch['pmsIp'], $plexWatch['pmsHttpsPort']);
							$myplexUrl = fsockopen ('my.plexapp.com', 443);

							if ($pmsHttp) {
								$statusPmsHttp = "<h5>Plex Media Server (HTTP):  <span class='label label-success'>Online</span></h5><br>";
							}

							else {
								$statusPmsHttp = "<h5>Plex Media Server (HTTP):  <span class='label label-important'>Offline</span></h5><br>";
							}

							if ($pmsHttps) {
								$statusPmsHttps = "<h5>Plex Media Server (HTTPS):  <span class='label label-success'>Online</span></h5><br>";
							}
							else {
								$statusPmsHttps = "<h5>Plex Media Server (HTTPS):  <span class='label label-important'>Offline</span></h5><br>";
							}
							
							if ($myplexUrl) {
								$statusMyplex = "<h5>myPlex: (<a href='https://my.plexapp.com'>my.plexapp.com</a>):  <span class='label label-success'>Online</span></h5><br>";
							}
							else {
								$statusMyplex = "<h5>myPlex: (<a href='https://my.plexapp.com'>my.plexapp.com</a>):  <span class='label label-important'>Offline</span></h5><br>";
							}
							
							echo "<div class='dashboard-status-instance'>";
								echo("$statusPmsHttp");
							echo "</div>";
							echo "<div class='dashboard-status-instance'>";
								echo("$statusPmsHttps");
							echo "</div>";
							echo "<div class='dashboard-status-instance'>";
								echo("$statusMyplex");
							echo "</div>";
							
							echo "<div id='currentLoad'>";
								echo " <img src='images/charts/cpu.png'></>";						
								echo " <img src='images/charts/mem.png'></>";						
								echo " <img src='images/charts/bw.png'></>";								
							echo "</div>";
						
					echo "</div>";
			echo "</div>";
		echo "</div>";
		echo "<div class='row-fluid'>";	
			echo "<div class='span12'>";
				echo "<div class='wellbg'>";
					echo "<div class='wellheader'>";
						echo "<div class='dashboard-wellheader'>";
							echo "<div id='currentActivityHeader'>";
								require("includes/current_activity_header.php");
							echo "</div>";
						echo "</div>";
					echo "</div>";
					echo "<div id='currentActivity'>";
						require("includes/current_activity.php");
					echo "</div>";	
				echo "</div>";			
			echo "</div>";
		echo "</div>";				
					
		echo "</div>";
		echo "<div class='row-fluid'>";
		
			date_default_timezone_set(@date_default_timezone_get());

			$db = new SQLite3($plexWatch['plexWatchDb']);
			
			$recentRequest = simplexml_load_file("".$plexWatchPmsUrl."/library/recentlyAdded?query=c&X-Plex-Container-Start=0&X-Plex-Container-Size=10") or die ("Failed to access Plex Media Server. Please check your server and config.php settings.");
		
			echo "<div class='wellbg'>";
				echo "<div class='wellheader'>";
					echo "<div class='dashboard-wellheader'>";
					echo "<h3>Recently Added</h3>";
					echo "</div>";
				echo "</div>";
				echo "<div class='dashboard-recent-media-row'>";
					echo "<ul class='dashboard-recent-media'>";
						// Run through each feed item
						foreach ($recentRequest->children() as $recentXml) {		              
						
							if ($recentXml['type'] == "season") {
								
								$recentArtUrl = "".$plexWatchPmsUrl."/photo/:/transcode?url=http://127.0.0.1:".$plexWatch['pmsHttpPort']."".$recentXml['art']."&width=320&height=160";                                        
								$recentThumbUrl = "".$plexWatchPmsUrl."/photo/:/transcode?url=http://127.0.0.1:".$plexWatch['pmsHttpPort']."".$recentXml['thumb']."&width=136&height=280";                                        

									echo "<div class='dashboard-recent-media-instance'>";
									echo "<li>";
									
									if($recentXml['thumb']) {
									
										echo "<div class='poster'><div class='poster-face'><a href='info.php?id=" .$recentXml['ratingKey']. "'><img src='".$recentThumbUrl."' class='poster-face'></img></a></div></div>";
									}else{
										echo "<div class='poster'><div class='poster-face'><a href='info.php?id=" .$recentXml['ratingKey']. "'><img src='images/poster.png' class='poster-face'></img></a></div></div>";
									}
									
									echo "<div class=dashboard-recent-media-metacontainer>";
									
									echo "<h3>Season ".$recentXml['index']."</h3>";
									
									
									$recentTime = $recentXml['addedAt'];
									$timeNow = time();
									$age = time() - strtotime($recentTime);
									include_once('includes/timeago.php');
									echo "<h4>Added ".TimeAgo($recentTime)."</h4>";
									
									echo "</div>";
									echo "</li>";
									echo "</div>";

						
							}else if ($recentXml['type'] == "movie") {				
							
								$recentArtUrl = "".$plexWatchPmsUrl."/photo/:/transcode?url=http://127.0.0.1:".$plexWatch['pmsHttpPort']."".$recentXml['art']."&width=320&height=160";                                        
								$recentThumbUrl = "".$plexWatchPmsUrl."/photo/:/transcode?url=http://127.0.0.1:".$plexWatch['pmsHttpPort']."".$recentXml['thumb']."&width=136&height=280";                                        
								
									echo "<div class='dashboard-recent-media-instance'>";
									echo "<li>";
									
									if($recentXml['thumb']) {
									
										echo "<div class='poster'><div class='poster-face'><a href='info.php?id=" .$recentXml['ratingKey']. "'><img src='".$recentThumbUrl."' class='poster-face'></img></a></div></div>";
									}else{
										echo "<div class='poster'><div class='poster-face'><a href='info.php?id=" .$recentXml->Video['ratingKey']. "'><img src='images/poster.png' class='poster-face'></img></a></div></div>";
									}
									
									echo "<div class=dashboard-recent-media-metacontainer>";
									$parentIndexPadded = sprintf("%01s", $recentXml['parentIndex']);
									$indexPadded = sprintf("%02s", $recentXml['index']);
									echo "<h3>".$recentXml['title']." (".$recentXml['year'].")</h3>";
									
									
									$recentTime = $recentXml['addedAt'];
									$timeNow = time();
									$age = time() - strtotime($recentTime);
									include_once('includes/timeago.php');
									echo "<h4>Added ".TimeAgo($recentTime)."</h4>";
									
									echo "</div>";
									echo "</li>";
									echo "</div>";
							
							}
						
						}
					echo "</ul>";
				echo "</div>";
			echo "</div>";
		echo "</div>";		
		?>
			
			

		<footer>
		
		</footer>
		
    </div><!--/.fluid-container-->
    
    <!-- javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-2.0.3.js"></script>
	<script src="js/bootstrap.js"></script>
	<script>
		
		function currentActivityHeader() {
			$('#currentActivityHeader').load('includes/current_activity_header.php');
		}
		setInterval('currentActivityHeader()', 15000);
	
	</script>
	<script>
		
		function currentActivity() {
			$('#currentActivity').load('includes/current_activity.php');
		}
		setInterval('currentActivity()', 15000);
	
	</script>
	

	
	



  </body>
</html>