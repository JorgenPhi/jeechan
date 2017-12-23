<?php
/* jeechan
 * https://github.com/JorgenPhi/jeechan
 * http://wakaba.c3.cx/shii/shiichan
 *
 * Add post
 */

require "includes/include.php";

// basic security measures-- don't leave home without 'em!
if (get_magic_quotes_gpc()) $_POST = array_map("stripslashes", $_POST);
$_POST = array_map("htmlspecialquotes", $_POST);
$_COOKIE = array_map("htmlspecialquotes", $_COOKIE);

$setting = getGlobalSettings() or fancyDie("Eh? Couldn't fetch the global settings file?!");
isset($_POST['bbs']) ? $lol = $_POST['bbs'] : $lol = $_GET['bbs'];
$local = getBoardSettings($lol);
if ($local) {
    foreach ($local as $name => $value) {
        $setting[$name] = $value;
    }
}

// If we're getting called to write a post, go for it.
if (isset($_GET['shiichan']) && $_GET['shiichan'] == "writenew") {
    $second = time();
    if ($setting['posticons']) {
        $icons = "<input type='radio' name='icon' value='noicon.png' checked> No icon<br>";
        $i = 0;
        $handle = opendir("includes/posticons");
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "noicon.png") {
                if ($i == 6) {
                    $icons .= "<br>";
                    $i = 0;
                }
                $icons .= "<input type='radio' name='icon' value='$file'><img src='/includes/posticons/$file'> ";
                $i++;
            }
        }
        closedir($handle);
        $icons .= "<br>The following posticons are for <b>admin use only</b>:<br>";
        $i = 0;
        $handle = opendir("includes/capcodes/icons");
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if ($i == 6) {
                    $icons .= "<br>";
                    $i = 0;
                }
                $icons .= "<input type='radio' name='icon' value='../capcodes/icons/$file'><img src='/includes/capcodes/icons/$file'> ";
                $i++;
            }
        }
        closedir($handle);
    } else {
        $icons = "<input type='hidden' name='icon' value='noicon.png'>Posticons are disabled.";
    }

    $html = file_get_contents("includes/skin/{$setting['skin']}/addthread.txt");
    if ($setting['encoding'] == "sjis") {
        $html = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=Shift_JIS'><style>* { font-family: Mona,'MS PGothic' !important } </style>", $html);
    } else {
        $html = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>", $html);
    }
    $html = str_replace("<%FORUMURL%>", $setting['urltoforum'], $html);
    $html = str_replace("<%POSTICONS%>", $icons, $html);
    $html = str_replace("<%FORUMNAME%>", $setting['forumname'], $html);
    $html = str_replace("<%BOARDNAME%>", $setting['boardname'], $html);
    $html = str_replace("<%BOARDURL%>", $_GET['bbs'], $html);
    if (isset($_COOKIE['jeeaccname'])) {
        $html = str_replace("<%NAMECOOKIE%>", "value='{$_COOKIE['jeeaccname']}'", $html);
    } else {
        $html = str_replace("<%NAMECOOKIE%>", "", $html);
    }
    if (isset($setting['adminsonly'])) {
        $html = str_replace("<%ADMINSONLY%>", "<h2 style='background:none;color:red'>Only administrators can post threads to this forum!</h2>", $html);
    } else {
        $html = str_replace("<%ADMINSONLY%>", "", $html);
    }
    $html = str_replace("<%STARTFORM%>", "<form name='post' action='post.php' method='POST'><input type='hidden' name='bbs' value='{$_GET['bbs']}'><input type='hidden' name='id' value='$second'><input type='hidden' name='shiichan' value='proper'>", $html);
    $html = str_replace("<%TEXTAREA%>", "<textarea rows='15' cols='75' name='mesg'></textarea><br><input type='submit' value='Create Thread'>", $html);
    echo $html;
    exit;
}

// If we're being called to write an advanced reply, write the advanced reply dammit.
if (isset($_GET['id'])) {
    $thread = file("{$_GET['bbs']}/dat/{$_GET['id']}.dat") or fancyDie("Couldn't open that thread");
    list ($threadname, $author, $lastposted) = explode("<=>", $thread[0]);
    $html = file_get_contents("includes/skin/{$setting['skin']}/addreply.txt");
    if (isset($_COOKIE['jeeaccname'])) {
        $html = str_replace("<%NAMECOOKIE%>", "value='{$_COOKIE['jeeaccname']}'", $html); 
    } else {
        $html = str_replace("<%NAMECOOKIE%>", "", $html);
    }
    if (!is_writable("{$_GET['bbs']}/dat/{$_GET['id']}.dat")) {
        $html = str_replace("<%THREADSTOPPED%>", "<h3>This thread is threadstopped!!</h3>", $html); 
    } else {
        $html = str_replace("<%THREADSTOPPED%>", "", $html);
    }
    $html = str_replace("<%THREADNAME%>", $threadname, $html);
    $html = str_replace("<%FORUMNAME%>", $setting['forumname'], $html);
    $html = str_replace("<%BOARDURL%>", $_GET['bbs'], $html);
    $html = str_replace("<%THREADID%>", $_GET['id'], $html);
    $html = str_replace("<%THREADLINK%>", linkToThread($_GET['bbs'], $_GET['id']), $html);
    $html = str_replace("<%FORUMURL%>", $setting['urltoforum'], $html);
    $html = str_replace("<%BOARDNAME%>", $setting['boardname'], $html);
    $html = str_replace("<%STARTFORM%>", "<form name='post' action='post.php' method='POST'><input type='hidden' name='bbs' value='{$_GET['bbs']}'><input type='hidden' name='id' value='{$_GET['id']}'><input type='hidden' name='shiichan' value='proper'>", $html);
    $html = str_replace("<%TEXTAREA%>", "<textarea rows='15' cols='75' name='mesg'></textarea><br><input type='submit' value='Add Reply'> <input name='sage' type='checkbox'> Sage?", $html);
    echo $html;
    exit;
} else if (isset($_GET['bbs'])) {
    echo "go fuck yourself";
    exit;
}


###########################
// AND AWAYYYY WE GOOO!!!!

// Check for POST and no in-forums spoofing
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    fancyDie("Trying to GET post.php?<meta http-equiv='refresh' content='0;url=.'>");
}

// Generate the date
$thisverysecond = time();

// mrvacbob 04-2009
$isnewthread = false;
if (isset($_POST['subj'])) {
    $_POST['id'] = null; // id is going to be the thread_num we are in
    $isnewthread = true;
}

// check for ban
if ($ban = getBan($_SERVER['REMOTE_ADDR'])) {
    fancyDie("<b>You have been banned from this message board.</b><p>The moderation team supplied this reason: <b>{$ban['pubreason']}</b>");
}

// check for flood
$lasttime = getFloodMarker($_SERVER['REMOTE_ADDR']);
if($lasttime != false && $lasttime + 5 > time()) {
    fancyDie("Please wait at least 5 seconds between posts!<p>You may have recieved this message from submitting your post more than once. Don't submit it again.");
}

// ENT_QUOTES thingy
function htmlspecialquotes($st) {
    return str_replace("&amp;#", "&#", htmlspecialchars("$st", ENT_QUOTES));
}

/* link shorten

function shorten($str){
  if(strlen($str) > 50) {
    $divide = round(strlen($str) / 3);
    if ($divide*2 > 50) {
      $divide = round(strlen($str) / 5);
     $second_string = substr($str,$divide*4,200);
    } else {
        $second_string = substr($str,$divide*2,200);
    }
   $first_string = substr($str,0,$divide);
   $short_string = $first_string . "..." . $second_string;
   $short_string = htmlspecialchars($short_string, ENT_NOQUOTES);
    } else {
 $short_string = $str;
 }
 return $short_string;
}*/


// for capcode functions
$threadstopwhendone = false;
$loggedin = false;

###################
// capcode post
if ($_POST['pass']) {
    $account = checkCredentials($_POST['name'], $_POST['pass']);
    if (is_array($account)) {
        $loggedin = true;
    } else if ($account == 1) {
        fancyDie("The password you supplied for that account name is incorrect.");
    }

    if (!$loggedin) {
        fancyDie('The account name you supplied is invalid.');
    }

    if (trim($account['capcode']) != '') {
        $_POST['name'] = "<b>" . $account['capcode'] . "</b>";
    } else {
        $_POST['name'] = "<b style='color:#f00'>$name</b>";
    }

    $idcrypt = "<b>(capped)</b> ";

    if (intval($account['level'] < 7500 && $setting['adminsonly'] && $_POST['subj'])) fancyDie("You need a userlevel of 7500 to start a thread."); // admins-only threads...
    if (!$isnewthread && !is_writable("{$_POST['bbs']}/dat/{$_POST['id']}.dat")) {
        if (intval($account['level']) < 6500) {
            fancyDie("You need a userlevel of 6500 to reply to this thread.");
        }
        chmod("{$_POST['bbs']}/dat/{$_POST['id']}.dat", 0666);
        $threadstopwhendone = true;
    }
} else {
#############################################
//////////////////// non-capcodes area
// str_replaces
    $_POST['mesg'] = str_replace("shiichan=proper", " lol what ", $_POST['mesg']);
    $_POST['name'] = str_replace(array("﹟", "＃", "♯"), "#", $_POST['name']); //  Unicode spoofs for tripcodes and capcodes

// ID hash 
    $idcrypt = " ";
    if ($setting['haship']) {
        $idcrypt .= "ID: " . substr(base64_encode(pack("H*", sha1($_SERVER['REMOTE_ADDR'] . date("d") . JEE_SALT))), 1, 8) . " ";
    }

#### funky tripcode time ###########
# no blank tripcodes plz
    if (preg_match("/\#$/", $_POST['name'], $match)) {
        $_POST['name'] = preg_replace("/\#$/", "", $_POST['name']);
    }
## ## ## Secure tripcodes courtesy of MrVacBob ## ## ##
# tripcode hashing, 2ch-style and modified Wakaba-style
    if (preg_match("/\#/", $_POST['name'])) {
        $_POST['name'] = str_replace("&#", "&%%%%%%", $_POST['name']); # otherwise HTML numeric entities screw up explode()!
        list ($name, $trip, $sectrip) = str_replace("&%%%%%%", "&#", explode("#", $_POST['name']));
        $_POST['name'] = $name;

        if ($trip != "") {
            $salt = strtr(preg_replace("/[^\.-z]/", ".", substr($trip . "H.", 1, 2)), ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
            $trip = substr(crypt($trip, $salt), -10);
        }

        if ($sectrip != "") {
            $sha = base64_encode(pack("H*", sha1($sectrip . JEE_SALT)));
            $sha = substr($sha, 0, 15);
            $trip .= "#" . $sha;
        }
    }
# End of tripcode section #############################

    if (strlen($_POST['name']) > 30) fancyDie("Your name is too damn long!");
// Certain things can only be done by admins.
    if(isset($_POST['icon'])) {
        if (strstr($_POST['icon'], "..")) fancyDie("When I say 'for admins only' I mean 'for admins only'!"); // admins-only icons...
    }
    if (isset($setting['adminsonly']) && $isnewthread) fancyDie("When I say 'for admins only' I mean 'for admins only'!"); // admins-only threads...
    if ($isnewthread && trim($_POST['subj']) == "") fancyDie("New threads must have a subject!"); // OP posts need a subject
    if (!$isnewthread && !is_writable("{$_POST['bbs']}/dat/{$_POST['id']}.dat")) fancyDie("You're not allowed to reply to this thread.<br>If you're making a new thread, <b>try entering a subject for it</b> DQN."); // threadstops
} // End of non-capcodes-only section ####
##########################################

//// anchor >>1 links
$al_bbs = $_POST['bbs'];
$al_thread = $_POST['id'];
$_POST['mesg'] = anchorLink($_POST['mesg']);

// linebreaks
if(isset($_POST['name'])) {
    $_POST['name'] = str_replace(array("\r\n", "\r", "\n"), " ", $_POST['name']);
}
if(isset($_POST['subj'])) {
    $_POST['subj'] = str_replace(array("\r\n", "\r", "\n"), " ", $_POST['subj']);
}
if(isset($_POST['icon'])) {
    $_POST['icon'] = str_replace(array("\r\n", "\r", "\n"), " ", $_POST['icon']);
}

// URL replace
function auto_url($txt) {

    # (1) catch those with url larger than 71 characters
    $pat = '/(http|ftp)+(?:s)?:(\\/\\/)'
        . '((\\w|\\.)+)(\\/)?(\\S){71,}/i';
    $txt = preg_replace($pat, "<a href=\"\\0\" target=\"_blank\">$1$2$3/...</a>",
        $txt);

    # (2) replace the other short urls provided that they are not contained inside an html tag already.
    $pat = '/(?<!href=\")(http|ftp)+(s)?:' .
        '(\\/\\/)((\\w|\\.)+) (\\/)?(\\S)/i';
    $txt = preg_replace($pat, "<a href=\"$0\" target=\"_blank\">$0</a> ",
        $txt);

    return $txt;
}

$_POST['mesg'] = auto_url($_POST['mesg']);
# # # Here be the quote parsing options. # # # 

// quote matching ... three times! BWAHAHAHAHAHAHAHAH
$_POST['mesg'] = preg_replace("/\n&gt; (.+)/i", "\n<span class='quote'>$1</span>", $_POST['mesg']);
$_POST['mesg'] = preg_replace("/^&gt; (.+)/i", "<span class='quote'>$1</span>", $_POST['mesg']);
$_POST['mesg'] = preg_replace("/<span class='quote'>&gt; (.+)/i", "<span class='quote'><span class='quote'>$1</span>", $_POST['mesg']);
$_POST['mesg'] = preg_replace("/<span class='quote'>&gt; (.+)/i", "<span class='quote'><span class='quote'>$1</span>", $_POST['mesg']);

// ABBC
// abbc changes \x01\x02 to <>
// (i guess all other php using it is exploitable)
//			mrvacbob 04-2009
$_POST['mesg'] = str_replace(array("\x01", "\x02"), "", $_POST['mesg']);
// TODO: Fix abbc. /e regexes are bad
//$_POST['mesg'] = abbc_proc($_POST['mesg']);
$_POST['mesg'] = str_replace(array("\r\n", "\r", "\n"), "", $_POST['mesg']);


// shiichan check
if ($_POST['shiichan'] != "proper") fancyDie("Whoever told you to click here is a mean person. Please tell them off.");

if ($isnewthread) {
    $_POST['sage'] = ""; // TODO: LOL I destroyed sage. I'll fix it later
}
if (isset($_POST['sage']) && trim($_POST['sage']) != "") $idcrypt .= "(sage)";

// Length checks
if (strlen($_POST['mesg']) == 0) fancyDie("You didn't write a post?!");
if (strlen($_POST['mesg']) > 10000) fancyDie("Thanks for your contribution, but it was too large.");
if (strlen($_POST['subj']) > 45) fancyDie("Subject is too long!");
if (count(explode("<br>", $_POST['mesg'])) > 100) fancyDie("Your post has far too many lines in it!");

// check for ID and board
if (!isset($_POST['bbs'])) fancyDie("No board specified to post to!");
if (!isset($_POST['id'])) fancyDie("No thread ID specified to post to!");
if (!is_dir($_POST['bbs'])) fancyDie("Board specified does not exist.");
if (!$isnewthread && !getThread($_POST['bbs'], $_POST['id'])) fancyDie("Thread ID specified does not exist.");

// Tripcode mohel TODO: Encrypted trips are blocked?  Or not? -- Yes they would be because tripcode gen is done before this section :) 
if ($_POST['name']) {
    $censorme = checkMohel($_POST['name'], $trip);

    if ($censorme == true) {
        echo "<b>Message from Mohel:</b> Your nickname was censored, for your own good.<p>";
        $_POST['name'] = "";
        $trip = '';
    }
}

// anonymous, we love you!
if ($_POST['name'] == "" && !$trip) $_POST['name'] = $setting['nameless'];

// It's time to actually write the post.

if ($isnewthread) { // If a new post
    addPostToDatabase($_POST['bbs'], 0, $_POST['name'], $trip, $_POST['subj'], $_POST['icon'], $posttime, $_POST['mesg'], $idcrypt, $_SERVER['REMOTE_ADDR']);
} else {
    addPostToDatabase($_POST['bbs'], $_POST['id'], $_POST['name'], $trip, null, null, $posttime, $_POST['mesg'], $idcrypt, $_SERVER['REMOTE_ADDR']);
}

setFloodMarker($_SERVER['REMOTE_ADDR']);

/*if (count(file("{$_POST['bbs']}/dat/{$_POST['id']}.dat")) > 999) { // Match anything with 1000 or greater replies. //TODO
    fwrite($handle, "Over 1000 Thread<><>$thisverysecond<>This thread has over 1000 replies.<br>You can't reply anymore.<>Over 1000<>1.1.1.1\n");
}*/


RebuildThreadList($_POST['bbs'], $_POST['id'], ($setting['neverbump'] && !$isnewthread ? true : $_POST['sage']), false);
?>
<html><title>Success</title>
    <meta http-equiv='refresh' content='1;url=<?= $setting['urltoforum'] ?><?= $_POST['bbs'] ?>/'>
<? readfile("includes/skin/{$setting['skin']}/success.txt"); ?>
    <br>
    <small><a href='<?= $setting['urltoforum'] ?><?= $_POST['bbs'] ?>/'>Click here to be forwarded manually</a></small>
    <hr>
    Powered by jeechan v.<?php echo $JEEVERSION;
