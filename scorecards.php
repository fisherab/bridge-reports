<?php
$eventNum = intval($_GET['event']);
$meta = json_decode(file_get_contents($eventNum . '/' . "meta.json"),true);
$numBoards = $meta['boards'];
$title = $meta['title'];
$boardNum = intval($_GET['board']);
$pairNum = intval($_GET['pair']);
$pairs = json_decode(file_get_contents($eventNum . '/' . "pairs.json"),true);
$resultsByPair = json_decode(file_get_contents($eventNum . '/' . "boards.json"),true);
$resultsForPair = $resultsByPair[$pairNum];
?>
<!DOCTYPE HTML>
<html>
<head>
<title><?php echo $title?></title>
</head>
<body>
<h1><?php echo $title?></h1> 
<table>
<caption>Results for one pair from their perspective</caption>  
<thead>
<tr><th colspan=10><?php echo $pairNum.' - '.$pairs[$pairNum]?></th></tr>
<tr><th>Board</th><th>Dir</th><th>Versus</th><th>Bid</th><th>By</th><th>Tks</th><th>+</th><th>-</th><th>Pts</th><th>%</th></tr>
</thead>
<tbody>
<?php

for($i = 1; $i <= $numBoards; ++$i) {
    $result = $resultsForPair[$i];
    if ($result == null) {
    	echo "<tr><td>".$i."</td><td colspan=9>Did not play</td></tr>";
    } else {
    	    if ($result['ns_pair'] == $pairNum) {
    	    	$dirn = "NS";
    	    	$versus = '<a href="scorecards.php?board='.$boardNum.'&event='.$eventNum.'&pair='.$result['ew_pair'].'">'.$pairs[$result['ew_pair']].'</a>';
    	    	$points = $result['ns_points'];
    	    	$perc = $result['ns_%'];
    	    	if ($result['score'] > 0) {
    	    	    $plus = $result['score'];
    	    	    $minus = null;
    	    	} elseif ($result['score'] < 0){
    	    	    $minus = -$result['score'];
    	    	    $plus = null;
    	    	} else {
    	    	    $plus = null;
    	    	    $minus = null;
    	    	}
    	    } else {
    	    	$dirn = "EW";
    	    	$versus = '<a href="scorecards.php?board='.$boardNum.'&event='.$eventNum.'&pair='.$result['ns_pair'].'">'.$pairs[$result['ns_pair']].'</a>';
    	    	$points = $result['ew_points'];
    	    	$perc = $result['ew_%'];
    	    	if ($result['score'] > 0) {
    	    	    $plus = $result['score'];
    	    	    $minus = null;
    	    	} elseif ($result['score'] < 0){
    	    	    $minus = -$result['score'];
    	    	    $plus = null;
    	    	} else {
    	    	    $plus = null;
    	    	    $minus = null;
    	    	}
    	    }
        echo "<tr><td>".$i."</td><td>".$dirn."</td><td>".$versus."</td><td>".$result['contract']."</td><td>".$result['declarer']."</td><td>".$result['result']."</td><td>".$plus."</td><td>".$minus."</td><td>".$points."</td><td>".$perc."</td></tr>";
    }
}
$in16 = ($boardNum -1) % 16;
$vul = ["None", "N/S", "E/W", "Both", "N/S", "E/W", "Both", "None", "E/W", "Both", "None", "N/S", "All", "None", "N/S", "E/W"][$in16];
$dealer = ["North", "East", "South", "West"][$in16 % 4];

?>
 </tbody>
 </table>
 <table>
 <caption>Results for one board</caption>
 <thead>
 <tr><th colspan=10><?php echo "Board No ".$boardNum.' '.$vul.' Vul Dealer '.$dealer?></th></tr>
 <tr><th>NS</th><th>EW</th><th>Bid</th><th>By</th><th>Ld</th><th>Tks</th><th>+Sc</th><th>-Sc</th><th>+</th><th>-</th></tr>
 </thead>
 <tbody>
 <?php
 
  $played = [];   
  for($p = 1; $p <= count($pairs); ++$p) {
    $result = $resultsByPair[$p][$boardNum];
    if ($result != null && $played[$result['ns_pair']] == false) {
    $played[$result['ns_pair']] = true;
    if ($result['declarer'] == "North" || $result['declarer'] == South) {
    if ($result['score'] > 0) {
    	$plus = $result['score'];
    	$minus = null;
    } elseif ($result['score'] < 0){
        $minus = -$result['score'];
        $plus = null;
    } else {
        $plus = null;
        $minus = null;
    }
    } else {
      if ($result['score'] < 0) {
    	$plus = -$result['score'];
    	$minus = null;
    } elseif ($result['score'] > 0){
        $minus = $result['score'];
        $plus = null;
    } else {
        $plus = null;
        $minus = null;
    }
    
    
    }
    echo "<tr><td>".$result['ns_pair']."</td><td>".$result['ew_pair']."</td><td>".$result['contract']."</td><td>".$result['declarer']."</td><td>Ld</td><td>".$result['result']."</td><td>".$plus."</td><td>".$minus."</td><td>".$result['ns_points']."</td><td>".$result['ew_points']."</td></tr>";
    } 
    }
    
    echo "<h2>Switch boards</h2><p>";
    for ($b = 1; $b <= $numBoards; ++$b) {
    	echo '<a href="scorecards.php?board='.$b.'&event='.$eventNum.'&pair='.$pairNum.'">'.$b.'</a> ';
    }
    echo "</p>"
 ?>         
           </tbody>
       </table>
 </html>    
