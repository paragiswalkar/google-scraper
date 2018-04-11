<?php 
include('simple_html_dom.php');
 
function strip_tags_content($text, $tags = '', $invert = FALSE) {
	/*
	This function removes all html tags and the contents within them
	unlike strip_tags which only removes the tags themselves.
	*/
	//removes <br> often found in google result text, which is not handled below
	$text = str_ireplace('<br>', '', $text);
 
	preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
	$tags = array_unique($tags[1]);
 
	if(is_array($tags) AND count($tags) > 0) {
		//if invert is false, it will remove all tags except those passed a
		if($invert == FALSE) {
			return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
		//if invert is true, it will remove only the tags passed to this function
		} else {
			return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
		}
	//if no tags were passed to this function, simply remove all the tags
	} elseif($invert == FALSE) {
		return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
	}
 
	return $text;
}
 
function file_get_contents_curl($url) {
	/*
	This is a file_get_contents replacement function using cURL
	One slight difference is that it uses your browser's idenity
	as it's own when contacting google. 
	*/
	$ch = curl_init();
 
	curl_setopt($ch, CURLOPT_USERAGENT,	$_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
 
	$data = curl_exec($ch);
	curl_close($ch);
 
	return $data;
}

$next = isset($_GET['next'])?$_GET['next']:0;
 
//Set query if any passed
$q = isset($_GET['q'])?'hl=en&'.'q='.urlencode($_GET['q']).'&oq='.urlencode($_GET['q']).'&source=web'.'&ie=UTF-8'.'&btnG=Search'.'&start='.$next:'none';

/*
create a simple_html_dom object from the retreived string
you could also perform file_get_html("http://...") instead of
file_get_contents_curl above, but it wouldn't change the default
User-Agent
*/
 
$html = file_get_html('http://www.google.com/search?'.$q);

$result = array();
 
foreach($html->find('div.g') as $g)
{
	/*
	each search results are in a list item with a class name 'g'
	we are seperating each of the elements within, into an array
 
	Titles are stored within <h3><a...>{title}</a></h3>
	Links are in the href of the anchor contained in the <h3>...</h3>
	Summaries are stored in a div with a classname of 's'
	*/
 
	$h3 = $g->find('h3.r', 0);
	$s = $g->find('div.s', 0);
	if($h3 != ""){
		$a = $h3->find('a', 0);
	}
	if($s != ""){
		$desc = $s->find('span.st',0);
		$cite = $s->find('cite',0);
	}
	
	$link = !empty($a)?$a->href:NULL;
	if (!preg_match('/^https?/', $link) && preg_match('/q=(.+)&amp;sa=/U', $link, $matches) && preg_match('/^https?/', $matches[1])) {
        $link = $matches[1];
    } else if (!preg_match('/^https?/', $link)) { // skip if it is not a valid link
        continue;    
    }
	
	$result[] = array('title' => strip_tags($a->plaintext), 
		'link' => $link, 
		'description' => !empty($desc)?strip_tags_content($desc->plaintext):NULL,
		'cite'=> !empty($cite)?strip_tags_content($cite->plaintext):NULL);
}
//Cleans up the memory 
$html->clear();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Google Web Search with Website Images</title>
<link href="css.css" rel="stylesheet" type="text/css">
</head>

<body onLoad="document.googleFrm.q.focus(); ">
<div align="center">
  <p>
    <script language="javascript">
function checkform (form) {
  if (form["q"].value == "") {
    alert("Please insert keyword(s) to search for");
    form["q"].focus();
    return false ; }
 return true; }
  </script>
  </p>
  <p>&nbsp;</p>
</div>
<div align="center">
<form name="googleFrm" method="get" action="search.php" onsubmit="return checkform(this);">
  <span class="white_bold_small"><strong>Search the Web</strong><br>
  </span>
    <input name="q" type="text" id="q" value="<?php echo isset($_GET['q'])?urldecode($_GET['q']):NULL;?>" size="60">
    <input type="submit" value="Search">
    <br>
	<br>
    <!--<input name="option" type="radio" id="image" value="0" checked> 
    Images <input name="option" type="radio" id="pr" value="1"> All</div>-->
	<br>
	<br>
</form>
</div>
<form name="googleFrm" method="get" action="search.php" onsubmit="return checkform(this);">
<input type="hidden" id='q' name='q' value="<?php echo isset($_GET['q'])?$_GET['q']:NULL;?>">
<select id="next" name="next" onchange="this.form.submit();">
<?php $i=$j=0; for($i=1;$i<=10;$i++){?>
<option value="<?php echo $j;?>" <?php echo (isset($_GET['next']) && ($_GET['next']==$j))?'selected':NULL;?>><?php echo $i;?></option>
<?php $j=$j+10; }?>
</select>
</form>
<?php 
if(isset($_GET['serialize']) && $_GET['serialize'] == '1')
{
	/* 
	if you pass serialize=1 to the script
	it will echo out a serialized string
	which can be unserialized back to an 
	array on a receiving script
	*/
	echo serialize($result);
}
else
{
	/* 
	Otherwise it prints out the array structure so that it
	is more human readible. You could instead perform a 
	foreach loop on the variable $result so that you can 
	organize the html output, or insert the data into a database
	*/
	//echo "<textarea style='width: 1024px; height: 600px;'>";
	?>
	<div style="width:100%;margin:0;padding:0;font-size:22px;">
	<?php foreach($result as $row){?>
	<div style="width:100%;margin:5px;padding:5px">
		<div style="width:100%;margin:0;padding:0"><a href="<?php echo $row['link'];?>" target="_blank"><?php echo $row['title'];?></a></div>
		<div style="width:100%;margin:0;padding:0;color:#006621;font-style:normal;"><?php echo $row['cite'];?></div>
		<div style="width:100%;margin:0;padding:0;color:#545454;line-height:1.4;word-wrap:break-word;"><?php echo $row['description'];?></div>
	</div>
	<?php }?>
	</div>
	<?php //echo "</textarea>";
}
?>
</body>
</html>