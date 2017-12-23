<?php
/* jeechan
* https://github.com/JorgenPhi/jeechan
* http://wakaba.c3.cx/shii/shiichan
*
* Basic includes
*/
require 'includes/settings.php';
require 'includes/lib/passwordcompat.php';
require 'includes/lib/abbc/abbc.lib.php';

 // ABBC BBCode processor.
// current version (int)

define("JEEVERSION", 6900);
$JEEVERSION = 6900;

if (!defined('PDO::ATTR_DRIVER_NAME')) {
    fancyDie("PDO isn't installed!  It is installed by default in PHP 5.1.0 and newer, you should upgrade your PHP version.  You can install PDO manually by running the command: <b>pear install pdo</b>");
}

try {
    $jee_db = new PDO(JEE_PDODSN, JEE_PDOUSER, JEE_PDOPASS, array(
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
}

catch(PDOException $ex) {
    fancyDie("Unable to connect to the database!  Have you configured settings.php properly?<br /><br />Error: " . $ex->getMessage());
}

// Report fatal errors.

function fancyDie($m) {
    global $JEEVERSION;
?>
    <title>Fatal Error</title>
    <style type="text/css">
        #logo {
            float: right;
            position: fixed;
            bottom: 0;
            right: 0;
            z-index: 999;
        }

        * {
            font-family: Tahoma, sans-serif
        }
    </style>
    <link rel="stylesheet" href="includes/skin/2ch/style.css">
    <table border="1" cellspacing="7" cellpadding="3" width="95%" bgcolor="#CCFFCC" align="center" class="mono">
    <tr>
        <td>
            <h1>Fatal error!</h1>

            <div id="logo"><a href="https://github.com/JorgenPhi/jeechan"><img src="logo.png" id="logo"
                                                                             title="Powered by jeechan"></a></div>
            <?php echo $m;?>
            <hr>
            Powered by jeechan <?php echo $JEEVERSION ?>
        </td>
    </tr></table><?php
    exit;
}

/* 
 * php delete function that deals with directories 
 * (c) Lewis Cowles
 */
function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
        
        foreach( $files as $file )
        {
            delete_files( $file );      
        }
      
        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}

function createBoardSchema($board) {
    global $jee_db;
    $board = preg_replace('/[^A-Za-z0-9_]+/', '', $board);
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'CREATE PROCEDURE `create_thread_%%BOARD%%` (`num` INT, `timestamp` INT) BEGIN INSERT IGNORE INTO `%%BOARD%%_threads` VALUES (num, `timestamp`, `timestamp`, 0, 0, 0); END;'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'CREATE PROCEDURE `update_thread_%%BOARD%%` (`tnum` INT, `p_timestamp` INT) BEGIN UPDATE `%%BOARD%%_threads` op SET op.time_last_modified = (COALESCE(GREATEST(op.time_last_modified, p_timestamp), op.time_op)), op.nreplies = (op.nreplies + 1) WHERE op.thread_num = tnum; END;'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'CREATE TABLE `%%BOARD%%` (`num` int(10) UNSIGNED NOT NULL, `poster_ip` decimal(39,0) UNSIGNED NOT NULL DEFAULT \'0\', `thread_num` int(10) UNSIGNED NOT NULL DEFAULT \'0\', `op` tinyint(1) NOT NULL DEFAULT \'0\', `timestamp` int(10) UNSIGNED NOT NULL, `capcode` varchar(255) DEFAULT NULL, `name` varchar(100) DEFAULT NULL, `trip` varchar(25) DEFAULT NULL, `title` varchar(100) DEFAULT NULL, `comment` text, `sticky` tinyint(1) NOT NULL DEFAULT \'0\', `locked` tinyint(1) NOT NULL DEFAULT \'0\', `poster_hash` varchar(8) DEFAULT NULL) DEFAULT CHARSET=utf8mb4;'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'CREATE TABLE `%%BOARD%%_threads` (`thread_num` int(10) UNSIGNED NOT NULL, `time_op` int(10) UNSIGNED NOT NULL, `time_last_modified` int(10) UNSIGNED NOT NULL, `nreplies` int(10) UNSIGNED NOT NULL DEFAULT \'0\', `sticky` tinyint(1) NOT NULL DEFAULT \'0\', `locked` tinyint(1) NOT NULL DEFAULT \'0\') DEFAULT CHARSET=utf8mb4;'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'ALTER TABLE `%%BOARD%%` ADD PRIMARY KEY (`num`), ADD KEY `thread_num_index` (`thread_num`,`num`), ADD KEY `op_index` (`op`), ADD KEY `name_trip_index` (`name`,`trip`), ADD KEY `trip_index` (`trip`), ADD KEY `poster_ip_index` (`poster_ip`), ADD KEY `timestamp_index` (`timestamp`);'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'ALTER TABLE `%%BOARD%%` MODIFY `num` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'ALTER TABLE `%%BOARD%%_threads` ADD PRIMARY KEY (`thread_num`), ADD KEY `time_op_index` (`time_op`), ADD KEY `time_last_modified_index` (`time_last_modified`), ADD KEY `sticky_index` (`sticky`), ADD KEY `locked_index` (`locked`);'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'CREATE TRIGGER `after_ins_%%BOARD%%` AFTER INSERT ON `%%BOARD%%` FOR EACH ROW BEGIN IF NEW.op = 1 THEN CALL create_thread_%%BOARD%%(NEW.num, NEW.timestamp); END IF; CALL update_thread_%%BOARD%%(NEW.thread_num, NEW.timestamp); END;'));
}

function addPostToDatabase($board, $thread_num, $name, $trip, $title, $icon, $posttime, $comment, $idcrypt, $ip) {

}

function getThread($board, $thread_num) {
    
}

function getSubjectTxt($board) {
    global $jee_db;
    $board = preg_replace('/[^A-Za-z0-9_]+/', '', $board);

    $stmt = $jee_db->prepare("SELECT `mg`.`title` as threadname, `mg`.`name` as author, `mg`.`capcode` as threadicon, `rt`.`thread_num` as id, `rt`.`nreplies` as replies, `rt`.`time_last_modified` as lasttime, `mg`.`trip` as trip FROM `{$board}_threads` rt LEFT JOIN `{$board}` mg ON mg.num = rt.thread_num ORDER BY rt.sticky DESC, rt.time_last_modified DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_NUM);
}

function deleteBoardSchema($board) {
    global $jee_db;
    if(!getBoardSettings($board)) {
        fancyDie("not a board");
    }
    $board = preg_replace('/[^A-Za-z0-9_]+/', '', $board);
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'DROP TABLE `%%BOARD%%`, `%%BOARD%%_threads`;'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'DROP PROCEDURE IF EXISTS `create_thread_%%BOARD%%`;'));
    $stmt = $jee_db->exec(str_replace('%%BOARD%%', $board, 'DROP PROCEDURE IF EXISTS `update_thread_%%BOARD%%`;'));
}

function getGlobalSettings() {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT `value` FROM `settings` WHERE `name`='_globalsettings' LIMIT 1");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        $row = json_decode($row["value"], true);
        if($row == null) {
            return false; // Invalid JSON
        }
        return $row;
    }

    return false; // No global settings
}

function setGlobalSettings($settings) {
    global $jee_db;
    $settings = json_encode($settings);
    if(!getGlobalSettings()) {
        // Create the key
        $stmt = $jee_db->prepare("INSERT INTO settings(name,value) VALUES('_globalsettings',:settings)");
        $stmt->bindValue(':settings', $settings, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        // Update
        $stmt = $jee_db->prepare("UPDATE settings SET value=:settings WHERE name='_globalsettings'");
        $stmt->bindValue(':settings', $settings, PDO::PARAM_STR);
        $stmt->execute();
    }
}

function getBoardList() {
    global $jee_db;
    $boards = array();
    $stmt = $jee_db->prepare("SELECT `name` FROM `settings` WHERE `name`!='_globalsettings' ORDER BY `name` DESC;");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        array_push($boards, $row["name"]);
    }
    return $boards;
}

function getBoardHead($board) {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT `head` FROM `settings` WHERE `name`=:board LIMIT 1");
    $stmt->bindValue(':board', $board, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        if($row["head"] == null) {
            return ""; // Not set
        }
        return $row["head"];
    }

    return false;
}

function setBoardHead($board, $head) {
    global $jee_db;
    $settings = json_encode($settings);
    $stmt = $jee_db->prepare("UPDATE settings SET head=:head WHERE name=:board");
    $stmt->bindValue(':board', $board, PDO::PARAM_STR);
    $stmt->bindValue(':head', $head, PDO::PARAM_STR);
    $stmt->execute();
}

function getBoardSettings($board) {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT `value` FROM `settings` WHERE `name`=:board LIMIT 1");
    $stmt->bindValue(':board', $board, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        $row = json_decode($row["value"], true);
        if($row == null) {
            return false; // Invalid JSON
        }
        return $row;
    }

    return false; // No board specific settings
}

function setBoardSettings($board, $settings) {
    global $jee_db;
    $settings = json_encode($settings);
    if(!getBoardSettings($board)) {
        // Create the key
        $stmt = $jee_db->prepare("INSERT INTO settings(name,value) VALUES(:board,:settings)");
        $stmt->bindValue(':board', $board, PDO::PARAM_STR);
        $stmt->bindValue(':settings', $settings, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        // Update
        $stmt = $jee_db->prepare("UPDATE settings SET value=:settings WHERE name=:board");
        $stmt->bindValue(':board', $board, PDO::PARAM_STR);
        $stmt->bindValue(':settings', $settings, PDO::PARAM_STR);
        $stmt->execute();
    }
}

function deleteBoardSettings($board) {
    global $jee_db;
    $stmt = $jee_db->prepare("DELETE FROM settings WHERE name=:board LIMIT 1");
    $stmt->bindValue(':board', $board, PDO::PARAM_STR);
    $stmt->execute();
}

function linkToThread($board, $thread, $posts = '') {
    if (JEE_PRETTYURLS) {
        return 'read.php/' . $board . '/' . $thread . '/' . $posts;
    } else {
        return 'read.php?b=' . $board . '&t=' . $thread . ($posts != '' ? ('&p=' . $posts) : '');
    }
}

function jeeHashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function newLoginKey() {
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $string = '';
    for ($i = 0; $i < 75; $i++) {
        $string.= $characters[rand(0, strlen($characters) - 1) ];
    }

    return $string;
}

function checkCredentials($username, $password) {
    global $jee_db;
    $account = accountByUsername($username);
    if (is_array($account)) {
        if (password_verify($password, $account['password'])) {
            return $account;
        }

        return 1; // Bad password
    }

    return 2; // Bad username
}

function checkLoginKey($username, $loginkey) {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT * FROM accounts WHERE username=:username AND loginkey=:loginkey LIMIT 1");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':loginkey', $loginkey, PDO::PARAM_STR);
    $stmt->execute();
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($accounts as $account) {
        return $account;
    }

    return 3; // Bad username/key
}

function accountByUsername($username) {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT * FROM accounts WHERE username=:username LIMIT 1");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($accounts as $account) {
        return $account;
    }

    return false; // No such account
}

function allAccounts() {
    global $jee_db;
    $stmt = $jee_db->query("SELECT * FROM accounts ORDER BY level DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function allBans() {
    global $jee_db;
    $stmt = $jee_db->query("SELECT * FROM bans ORDER BY at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function allMohels() {
    global $jee_db;
    $stmt = $jee_db->query("SELECT * FROM mohel ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateAccountLevel($username, $level) {
    global $jee_db;
    $stmt = $jee_db->prepare("UPDATE accounts SET level=:level WHERE username=:username LIMIT 1");
    $stmt->bindValue(':level', intval($level) , PDO::PARAM_INT);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}

function updateAccountPassword($username, $password) {
    global $jee_db;
    $stmt = $jee_db->prepare("UPDATE accounts SET password=:password,loginkey=:loginkey WHERE username=:username LIMIT 1");
    $stmt->bindValue(':password', jeeHashPassword($password) , PDO::PARAM_STR);
    $stmt->bindValue(':loginkey', newLoginKey() , PDO::PARAM_STR);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}

function updateAccountCapcode($username, $capcode) {
    global $jee_db;
    $stmt = $jee_db->prepare("UPDATE accounts SET capcode=:capcode WHERE username=:username LIMIT 1");
    $stmt->bindValue(':capcode', $capcode, PDO::PARAM_STR);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}

function addAccount($username, $password, $level) {
    global $jee_db, $myaccount;
    $stmt = $jee_db->prepare("INSERT INTO accounts(username,password,loginkey,addedby,level) VALUES(:username,:password,:loginkey,:addedby,:level)");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':password', jeeHashPassword($password) , PDO::PARAM_STR);
    $stmt->bindValue(':loginkey', newLoginKey() , PDO::PARAM_STR);
    $stmt->bindValue(':addedby', (is_array($myaccount) ? intval($myaccount['id']) : 0) , PDO::PARAM_INT);
    $stmt->bindValue(':level', intval($level) , PDO::PARAM_INT);
    $stmt->execute();
}

function addMohel($mohel) {
    global $jee_db, $myaccount;
    $stmt = $jee_db->prepare("INSERT INTO mohel(mohel) VALUES(:mohel)");
    $stmt->bindValue(':mohel', $mohel, PDO::PARAM_STR);
    $stmt->execute();
}

function addban($ip, $pubres, $privres, $bannedby) {
    global $jee_db;
    $stmt = $jee_db->prepare("INSERT INTO bans(ip,pubreason,privreason,bannedby,at) VALUES(:ip,:pubres,:privres,:bannedby,:at)");
    $stmt->bindValue(':ip', trim($ip), PDO::PARAM_STR);
    $stmt->bindValue(':pubres', $pubres, PDO::PARAM_STR);
    $stmt->bindValue(':privres', $privres, PDO::PARAM_STR);
    $stmt->bindValue(':bannedby', $bannedby, PDO::PARAM_STR);
    $stmt->bindValue(':at', time() , PDO::PARAM_INT);
    $stmt->execute();
}

function checkMohel($name, $trip) {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT COUNT(*) FROM mohel WHERE mohel=:mohel LIMIT 1");
    $stmt->bindValue(':mohel', $name, PDO::PARAM_STR);
    $stmt->execute();
    if (intval($stmt->fetchColumn()) > 0) {
        return true;
    }

    $stmt->bindValue(':mohel', $name . '#' . $trip, PDO::PARAM_STR);
    $stmt->execute();
    if (intval($stmt->fetchColumn()) > 0) {
        return true;
    }

    $stmt->bindValue(':mohel', '#' . $trip, PDO::PARAM_STR);
    $stmt->execute();
    if (intval($stmt->fetchColumn()) > 0) {
        return true;
    }

    return false;
}

function getBan($ip) {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT * FROM bans WHERE ip=:ip LIMIT 1");
    $stmt->bindValue(':ip', trim($ip), PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        return $row;
    }

    return false;
}

function deleteAccountByUsername($username) {
    global $jee_db;
    $stmt = $jee_db->prepare("DELETE FROM accounts WHERE username=:username LIMIT 1");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
}

function deleteMohel($id) {
    global $jee_db;
    $stmt = $jee_db->prepare("DELETE FROM mohel WHERE id=:id LIMIT 1");
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
}

function deleteBan($id) {
    global $jee_db;
    $stmt = $jee_db->prepare("DELETE FROM bans WHERE id=:id LIMIT 1");
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
}

function setFloodMarker($ip) {
    global $jee_db;
    if(!getFloodMarker($ip)) {
        // Create the key
        $stmt = $jee_db->prepare("INSERT INTO `flood` VALUES (:ip, :time)");
        $stmt->bindValue(':ip', trim($ip), PDO::PARAM_STR);
        $stmt->bindValue(':time', time(), PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Update
        $stmt = $jee_db->prepare("UPDATE `flood` SET `time`=:time WHERE `ip`=:ip");
        $stmt->bindValue(':ip', trim($ip), PDO::PARAM_STR);
        $stmt->bindValue(':time', time(), PDO::PARAM_INT);
        $stmt->execute();
    }
}

function getFloodMarker($ip) {
    global $jee_db;
    $stmt = $jee_db->prepare("SELECT `time` FROM `flood` WHERE ip=:ip LIMIT 1");
    $stmt->bindValue(':ip', trim($ip), PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        return $row['time'];
    }

    return false;
}

function cleanFloodMarkers() {
    //TODO
}

/* ADMIN FUNCTIONS */

function doesHavePermisison($userLevel, $reuqiredLevel) {
    if ($userLevel < $reuqiredLevel) fancyDie("You don't have permission for that.");
}

function printSuccess($message) { ?>
    <link rel="stylesheet" href="admin.css"><h1>Success</h1>
    <div id="logo"><a href="https://github.com/JorgenPhi/jeechan"><img src="logo.png" id="logo" title="Powered by jeechan"></a></div>
    <p><?php echo $message ?></p>
    <a href="admin.php">Back to Admin Panel</a>
    <?php
}

function icons($i, $threadicon) {
    global $setting;
    if ($setting['posticons']) return "<img src='includes/posticons/$threadicon'>";
    return $i + 1;
}

function PrintPost($number, $name, $trip, $date, $id, $message, $postfile, $tid, $boardname) {
    global $setting;
    if ($date == 1234) return null;
    $post = $postfile;
    $post = str_replace("<%NUMBER%>", "<a href='javascript:quote($number,\"post$tid\");' class='unstyled'>$number</a>", $post);
    $number % 2 ? $post = str_replace("<%CSSHELP%>", "even", $post) : $post = str_replace("<%CSSHELP%>", "odd", $post);
    $post = str_replace("<%NAME%>", $name, $post);
    $post = str_replace("<%TRIP%>", $trip, $post);
    $post = str_replace("<%DATE%>", date("y/m/d(D)H:i:s", $date) , $post);
    $post = str_replace("<%ID%>", $id, $post);
    if ($tid != 1 && $number != 1) {
        $messy = explode("<br />", $message);
        $message = "";
        for ($i = 1; $i <= $setting['fplines']; $i++)
        if ($messy) {
            $message.= array_shift($messy);
            $message.= "<br />";
        }

        if ($messy) $message.= "<i>(<a href='" . linkToThread($boardname, $tid, $number) . "'>Post truncated.</a>)</i>";
    }

    $post = str_replace("<%MESSAGE%>", $message, $post);
    return $post;
}

function PrintPages($numposts, $boardname, $threadid, $postsperpage) {
    $moot = "<span class='pages'>Pages:";
    for ($i = 1; $i <= ($numposts / $postsperpage) + 1; $i++) {
        $print = $postsperpage * ($i - 1) + 1;
        $pc = $print . "-";
        $tmp = $postsperpage * $i;
        if ($tmp < $numposts) $pc.= $tmp;
        $moot.= "<a href='" . linkToThread($boardname, $threadid, $pc) . "'>$print</a> ";
    }

    return $moot . "</span>";
}

function PrintThread($boardname, $threadid, $postarray, $isitreadphp) { //TODO
    global $setting, $JEEVERSION;
    $postthing = 1;
    $postfile = file_get_contents("includes/skin/".$setting['skin']."/post.txt");
    $thread = file("$boardname/dat/$threadid.dat");
    $numposts = count($thread) - 1;
    if (!$isitreadphp) {
        $postthing = $threadid;
        $start = $numposts - $setting['fpposts'] + 1;
        if ($start < 1) $start = 1;
        $end = $numposts;
        $postarray = array(
            "$start-$end"
        );
    }

    list($threadname, $author, $threadicon) = explode("<=>", $thread[0]);
    if ($isitreadphp) {
        $top = file_get_contents("includes/skin/".$setting['skin']."/threadtop.txt");
        if (file_exists("option.txt")) $option = "<div class='option'>" . file_get_contents("option.txt") . "</div>";
        else $option = "";
        $setting['posticons'] ? $top = str_replace("<%THREADICON%>", "<img src='includes/posticons/$threadicon' alt='thread icon'>", $top) : $top = str_replace("<%THREADICON%>", "", $top);
        $top = str_replace("<%FORUMNAME%>", $setting['forumname'], $top);
        $top = str_replace("<%FORUMURL%>", $setting['urltoforum'], $top);
        $top = str_replace("<%BOARDURL%>", $boardname, $top);
        $top = str_replace("<%BOARDNAME%>", $setting['boardname'], $top);
        $top = str_replace("<%OPTION%>", $option, $top);
        if ($setting['encoding'] == "sjis") $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=Shift_JIS'><style>* { font-family: Mona,'MS PGothic' !important } " . abbc_css() . "</style>", $top);
        else $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=UTF-8'><style>" . abbc_css() . "</style>", $top);
        $top = str_replace("<%THREADNAME%>", $threadname, $top);
        $isitreadphp ? $top = str_replace("<%PAGES%>", PrintPages($numposts, $boardname, $threadid, $setting['postsperpage']) , $top) : $top = str_replace("<%PAGES%>", "", $top);
        $top = str_replace("<%STARTFORM%>", "<form name='post$postthing' action='post.php' method='POST'><input type='hidden' name='bbs' value='$boardname'><input type='hidden' name='id' value='$threadid'><input type='hidden' name='shiichan' value='proper'>", $top);
        $return = $top;
    }
    else {
        $top = file_get_contents("includes/skin/".$setting['skin']."/smallthreadtop.txt");
        $top = str_replace("<%THREADNAME%>", "<a name='$threadid' href='" . linkToThread($boardname, $threadid, "1-{".$setting['postsperpage']."}") . "' class='unstyled'>$threadname</a>", $top);
        $top = str_replace("<%STARTFORM%>", "<form name='post$postthing' action='post.php' method='POST'><input type='hidden' name='bbs' value='$boardname'><input type='hidden' name='id' value='$threadid'><input type='hidden' name='shiichan' value='proper'>", $top);
        $return = $top;
    }

    // Always show the first post on the front page.

    if (!$isitreadphp && $start != 1) {
        list($name, $trip, $date, $message, $id, $ip) = explode("<>", $thread[1]);
        if ($trip) $trip = "#" . $trip;
        if ($isitreadphp) $return.= PrintPost(1, $name, $trip, $date, $id, $message, $postfile, 1, $boardname);
        else $return.= PrintPost(1, $name, $trip, $date, $id, $message, $postfile, $threadid, $boardname);

        // The latest replies are hidden... but gotta have skins!

        $hidden = file_get_contents("includes/skin/".$setting['skin']."/hidden.txt");
        $hidden = str_replace("<%FEW%>", $setting['fpposts'], $hidden);
        $hidden = str_replace("<%READ%>", linkToThread($boardname, $threadid, "1-{".$setting['postsperpage']."}") , $hidden);
        $return.= $hidden;
    }

    foreach($postarray as $apost) {
        list($start, $end) = explode('-', $apost);
        if (strpos($start, 'l') === 0) {
            $start = ($numposts - intval(substr($start, 1))) + 1;
            $end = $numposts;
        }

        if ($start < 1) $start = 1;
        if ($end == "")
        if (strstr($apost, "-")) $end = $numposts;
        else $end = $start;
        if ($end > $numposts) $end = $numposts;
        if ($start > $numposts) $start = $numposts;
        if ($start <= $end) {
            for ($i = $start; $i <= $end; $i++) {
                list($name, $trip, $date, $message, $id, $ip) = explode("<>", $thread[$i]);
                if ($trip) $trip = "#" . $trip;
                if ($isitreadphp) $return.= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, 1, $boardname);
                else $return.= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, $threadid, $boardname);
            }
        }
        else {
            for ($i = $start; $i >= $end; $i--) {
                if ($end < 1) $end = 1;
                if ($start > $numposts) $start = $numposts;
                list($name, $trip, $date, $message, $id, $ip) = explode("<>", $thread[$i]);
                if ($trip) $trip = "#" . $trip;
                if ($isitreadphp) $return.= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, 1, $boardname);
                else $return.= PrintPost($i, $name, $trip, $date, $id, $message, $postfile, $threadid, $boardname);
            }
        }
    }

    if ($isitreadphp) { // read.php takes its skin file
        $bottom = file_get_contents("includes/skin/".$setting['skin']."/threadbottom.txt");
        $bottom = str_replace("<%JEEVERSION%>", $JEEVERSION, $bottom);
    }
    else $bottom = file_get_contents("includes/skin/".$setting['skin']."/smallthreadbottom.txt");
    $bottom = str_replace("<%NUMPOSTS%>", $numposts + 1, $bottom);
    if (!is_writable("$boardname/dat/$threadid.dat")) $bottom = str_replace("<%TEXTAREA%>", "This thread is threadstopped. You can't reply anymore.", $bottom);
    else
    if ($setting['namefield']) $bottom = str_replace("<%TEXTAREA%>", "<textarea rows='5' cols='64' name='mesg'></textarea><br /><input type='submit' value='Add Reply'> Name <input name='name'> &nbsp;&nbsp;&nbsp; <input name='sage' type='checkbox'> Sage<br /><a href='" . linkToThread($boardname, $threadid, "1-{.".$setting['postsperpage']."}") . "'>First Page</a> - <a href='" . linkToThread($boardname, $threadid, "l{".$setting['postsperpage']."}") . "'>Last ".$setting['postsperpage']."</a> - <a href='" . linkToThread($boardname, $threadid) . "'>Entire Thread</a> - <a href='<%REPLYLINK%>' title='Advanced reply'>Advanced Reply</a>", $bottom);
    else $bottom = str_replace("<%TEXTAREA%>", "<textarea rows='5' cols='64' name='mesg'></textarea><br /><input type='submit' value='Add Reply'> &nbsp; <input name='sage' type='checkbox'> Sage<br /><br /><a href='" . linkToThread($boardname, $threadid) . "'>Entire Thread</a> - <a href='" . linkToThread($boardname, $threadid, "1-{".$setting['postsperpage']."}") . "'>First Page</a> - <a href='" . linkToThread($boardname, $threadid, "l{".$setting['postsperpage']."}") . "'>Last ".$setting['postsperpage']."</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <small><a href='<%REPLYLINK%>' title='Advanced reply'>Advanced Reply</a></small>", $bottom);
    $bottom = str_replace("<%REPLYLINK%>", "post.php?id=$threadid&amp;bbs=$boardname", $bottom);
    $bottom = str_replace("<%ADMINLINK%>", "<a href='admin.php?task=manage&amp;bbs=$boardname&amp;tid=$threadid&amp;st=$start&amp;ed=$end'>Manage</a>", $bottom);
    $return.= $bottom;
    return $return;
}

// ### shall we rewrite index.html?

function RebuildThreadList($bbs, $thisid, $sage, $rmthread) { //TODO
    global $setting, $JEEVERSION;
    $subject = file("$bbs/subject.txt");
    if ($thisid != 1) {
        global $_POST, $thisverysecond;
        while (list($value, $line) = each($subject)) {
            list($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $line);
            if ($id == $thisid) {
                $slice1 = array_slice($subject, 0, $value);
                $slice2 = array_slice($subject, $value + 1, count($subject));
                $replies = count(file("$bbs/dat/$thisid.dat")) - 1;
                if (!$sage or !$slice1) {
                    $subject = array_merge($slice1, $slice2);
                    if (!$rmthread) array_unshift($subject, "$threadname<>$author<>$threadicon<>$id<>$replies<>".$_POST['name']."<>$thisverysecond\n");
                    break;
                }
                else {
                    if (!$rmthread) array_push($slice1, "$threadname<>$author<>$threadicon<>$id<>$replies<>".$_POST['name']."<>$thisverysecond\n");
                    $subject = array_merge($slice1, $slice2);
                    break;
                }
            }
        }

        $f = fopen("$bbs/subject.txt", "w") or die("couldn't write to subject.txt");
        foreach($subject as $line) fwrite($f, $line);
        fclose($f);
    }

    $f = fopen("$bbs/index.html", "w") or die("couldn't write to index.html");
    if (file_exists("option.txt")) $option = "<div class='option'>" . file_get_contents("option.txt") . "</div>";
    else $option = "";
    $top = file_get_contents("includes/skin/".$setting['skin']."/boardtop.txt");
    $top = str_replace("<%POST%>", "<form action='post.php'><input type='hidden' name='shiichan' value='writenew'><input type='hidden' name='bbs' value='$bbs'><input type='submit' value='New Thread'></form>", $top);
    $top = str_replace("<%FORUMURL%>", $setting['urltoforum'], $top);
    $top = str_replace("<%BOARDURL%>", $bbs, $top);
    $top = str_replace("<%FORUMNAME%>", $setting['forumname'], $top);
    $top = str_replace("<%BOARDNAME%>", $setting['boardname'], $top);
    $top = str_replace("<%OPTION%>", $option, $top);
    if ($setting['encoding'] == "sjis") $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=Shift_JIS'><style>* { font-family: Mona,'MS PGothic' !important }" . abbc_css() . "</style>", $top);
    else $top = str_replace("<%ENCODING%>", "<META http-equiv='Content-Type' content='text/html; charset=UTF-8'><style>" . abbc_css() . "</style>", $top);
    fputs($f, $top);
    if (!$subject) fputs($f, "<tr><td colspan='5'><p style='text-align:center; padding: 1em'>This forum has no threads in it.</p></td></tr>");
    else {
        for ($i = 0; $i < $setting['fpthreads']; $i++) {
            if (!$subject[$i]) break;

            list($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $subject[$i]);
            $time = date("y/m/d(D)H:i:s", $lasttime);
            $icon = icons($i, $threadicon);
            $pages = ceil($replies / $setting['postsperpage']);
            $last = ($pages - 1) * $setting['postsperpage'];
            fputs($f, "<tr><td><a href='" . linkToThread($bbs, $id) . "'>$icon</a> </td><td><a href='$bbs/#$id'>$threadname</a>");
            if ($pages > 1) {
                fputs($f, " ( ");
                for ($j = 0; $j < $pages && $j < 7; $j++) {
                    $jam = $j * $setting['postsperpage'] + 1;
                    $jelly = $jam - 1 + $setting['postsperpage'];
                    fputs($f, "<a href='" . linkToThread($bbs, $id, "$jam-$jelly") . "'>$jam</a> ");
                }

                if ($pages > 6) {
                    fputs($f, "... <a href='" . linkToThread($bbs, $id, "$last-") . "'>Last page</a> ");
                }

                fputs($f, ")");
            }

            fputs($f, "</td><td>$author</td><td>$replies</td><td nowrap><small><a href='" . linkToThread($bbs, $id, "$last-") . "'>$time</a></small></td></tr>");
        }

        for ($i = $setting['fpthreads']; $i < $setting['fpthreads'] + $setting['additionalthreads']; $i++) {
            if (!$subject[$i]) break;

            list($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $subject[$i]);
            $time = date("y/m/d(D)H:i:s", $lasttime);
            $icon = icons($i, $threadicon);
            fputs($f, "<tr><td><a href='" . linkToThread($bbs, $id, "1-{".$setting['postsperpage']."}") . "'>$icon</a></td><td><a href='" . linkToThread($bbs, $id, "l{".$setting['postsperpage']."}") . "'>$threadname</a></td><td>$author</td><td>$replies</td><td nowrap><small>$time</small></td></tr>");
        }
    }

    $middle = file_get_contents("includes/skin/".$setting['skin']."/boardmiddle.txt");
    $middle = str_replace("<%BOARDURL%>", $bbs, $middle);
    $middle = str_replace("<%HEADTXT%>", getBoardHead($bbs) , $middle);
    fputs($f, $middle);
    for ($i = 0; $i < $setting['fpthreads']; $i++) {
        if (!isset($subject[$i])) break;

        list($threadname, $author, $threadicon, $id, $replies, $last, $lasttime) = explode("<>", $subject[$i]);
        fputs($f, PrintThread($bbs, $id, array(
            "0"
        ) , false));
    }

    $bottom = file_get_contents("includes/skin/".$setting['skin']."/boardbottom.txt");
    $bottom = str_replace("<%JEEVERSION%>", $JEEVERSION, $bottom);
    fputs($f, $bottom);
    fclose($f);
}

function _anchorLink($matches) {
    global $al_bbs, $al_thread;
    $trailing_comma = false;
    if (substr($matches[0], -1) == ',') {
        $trailing_comma = true;
        $matches[0] = substr($matches[0], 0, -1);
        $matches[1] = substr($matches[1], 0, -1);
    }

    return '<a href="' . linkToThread($al_bbs, $al_thread, $matches[1]) . '">' . $matches[0] . '</a>' . ($trailing_comma ? ',' : '');
}

function anchorLink($message) {
    return preg_replace_callback('/&gt;&gt;([\d,lqr-]+)/', '_anchorLink', $message);
}
