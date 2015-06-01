<?php
date_default_timezone_set(@date_default_timezone_get());

$guisettingsFile = dirname(__FILE__) . '/../config/config.php';
if (file_exists($guisettingsFile)) {
	require_once($guisettingsFile);
} else {
	error_log('PlexWatchWeb :: Config file not found.');
	echo "Config file not found";
	exit;
}

$plexWatchPmsUrl = "http://".$plexWatch['pmsIp'].":".$plexWatch['pmsHttpPort']."";

$db = dbconnect();

if (isset($_POST['user'])) {
	$user = $db->escapeString($_POST['user']);
} else {
	error_log('PlexWatchWeb :: POST parameter "user" not found.');
	echo "user field is required.";
	exit;
}

$plexWatchDbTable = dbTable('user');
$recentlyWatchedResults = $db->query("SELECT title, user, platform, time, stopped, ip_address, xml, paused_counter FROM ".$plexWatchDbTable." WHERE user = '$user' ORDER BY time DESC LIMIT 10");

echo "<ul class='dashboard-recent-media'>";
// Run through each feed item
while ($recentlyWatchedRow = $recentlyWatchedResults->fetchArray()) {
	$request_url = $recentlyWatchedRow['xml'];
	$recentXml = simplexml_load_string($request_url);
	if (!empty($plexWatch['myPlexAuthToken'])) {
		$myPlexAuthToken = "?X-Plex-Token=" . $plexWatch['myPlexAuthToken'];
	} else {
		$myPlexAuthToken = '';
	}
	$recentMetadata = $plexWatchPmsUrl."/library/metadata/".$recentXml['ratingKey'].$myPlexAuthToken;
	if ($recentThumbUrlRequest = @simplexml_load_file($recentMetadata)) {
		$thumbUrl = 'images/poster.png';
		if ($recentXml['type'] == "episode") {
			$recentThumbUrl = $recentThumbUrlRequest->Video['parentThumb']."&width=136&height=280";
			$recentgThumbUrl = $recentThumbUrlRequest->Video['grandparentThumb']."&width=136&height=280";
			if ($recentThumbUrlRequest->Video['parentThumb']) {
				$thumbUrl = 'includes/img.php?img='.urlencode($recentThumbUrl);
			} else if ($recentThumbUrlRequest->Video['grandparentThumb']) {
				$thumbUrl = 'includes/img.php?img='.urlencode($recentgThumbUrl);
			}
		} else if ($recentXml['type'] == "movie") {
			$recentThumbUrl = $recentThumbUrlRequest->Video['thumb']."&width=136&height=280";
			if ($recentThumbUrlRequest->Video['thumb']) {
				$thumbUrl = 'includes/img.php?img='.urlencode($recentThumbUrl);
			}
		} else if ($recentXml['type'] == "clip") {
			$recentThumbUrl = $recentThumbUrlRequest->Video['thumb']."&width=136&height=280";
			$thumbUrl = 'includes/img.php?img='.urlencode($recentThumbUrl);
		}
	} else {
		continue;
	}
	echo "<div class='dashboard-recent-media-instance'>";
		echo "<li>";
			echo "<div class='poster'><div class='poster-face'><a href='info.php?id=" .$recentXml['ratingKey']. "'>";
			echo "<img src='".$thumbUrl."' class='poster-face'></a></div></div>";
			echo "<div class=dashboard-recent-media-metacontainer>";
				if ($recentXml['type'] == "episode") {
					$parentIndexPadded = sprintf("%01s", $recentXml['parentIndex']);
					$indexPadded = sprintf("%02s", $recentXml['index']);
					echo "<h3>Season ".$parentIndexPadded.", Episode ".$indexPadded."</h3>";
				} else { // "movie" || "clip"
					echo "<h3>".$recentXml['title']." (".$recentXml['year'].")</h3>";
				}
			echo "</div>";
		echo "</li>";
	echo "</div>";
}
echo "</ul>";
?>