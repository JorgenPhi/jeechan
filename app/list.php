<?php
require "includes/include.php";

if (!$_GET['bbs']) die("Specify a BBS, please.");
$_GET['bbs'] = htmlspecialchars($_GET['bbs']);
$setting = getGlobalSettings() or fancyDie("Eh? Couldn't fetch the global settings file?!");
$local = getBoardSettings($_GET['bbs']);
if ($local) {
	foreach ($local as $name => $value) {
    	$setting[$name] = $value;
    }
}
$top = file_get_contents("includes/skin/{$setting['skin']}/boardtop.txt");
$top = str_replace("<%POST%>", "#", $top);
$top = str_replace("<%FORUMURL%>", $setting['urltoforum'], $top);
$top = str_replace("<%BOARDURL%>", $_GET['bbs'], $top);
$top = str_replace("<%FORUMNAME%>", $setting['forumname'], $top);
$top = str_replace("<%BOARDNAME%>", $setting['boardname'], $top);
$top = str_replace("<%OPTION%>", "", $top);
if ($setting['encoding'] == "sjis") $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=Shift_JIS'><style>* { font-family: Mona,'MS PGothic' !important }</style>", $top);
else $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>", $top);
echo $top;

if(!getBoardSettings($_GET['bbs'])) {
    echo "<tr><td colspan='5'><p style='text-align:center; padding: 1em'>This forum does not exist.</p></td></tr>";
    exit;
}

$list = getSubjectTxt($_GET['bbs']);
if (!$list || $list == array()) {
    echo "<tr><td colspan='5'><p style='text-align:center; padding: 1em'>This forum has no threads in it.</p></td></tr>";
    exit;
}
foreach ($list as $thread) {
    list ($threadname, $author, $threadicon, $id, $replies, $lasttime, $trip) = $thread;
    $replies++; // Count OP
    $time = date("j M Y H:i", intval($lasttime));
    $icon = icons(@$i, $threadicon);
    echo "<tr><td><a href='" . linkToThread($_GET['bbs'], $id, "1-{$setting['postsperpage']}") . "'>$icon</a></td><td><a href='" . linkToThread($_GET['bbs'], $id, "l".$setting['postsperpage']) . "'>$threadname</a></td><td>$author</td><td>$replies</td><td nowrap><small>$time</small></td></tr>";
}