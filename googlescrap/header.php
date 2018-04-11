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
<form name="googleFrm" method="get" action="search.php" onsubmit="return checkform(this);">
  <div align="center"><span class="white_bold_small"><strong>Search the Web</strong><br>
  </span>
    <input name="q" type="text" id="q" size="60">
    <input type="submit" value="Search">
    <br>
	<br>
    <!--<input name="option" type="radio" id="image" value="0" checked> 
    Images <input name="option" type="radio" id="pr" value="1"> All</div>-->
	<br>
	<br>
</form>
