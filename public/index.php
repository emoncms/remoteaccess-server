<?php
define('DEBUG', true);

include "lib/core.php";

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
}
// Basic session
session_start();
$session = array("valid"=>false, "username"=>false,"password"=>false);
if (isset($_SESSION['username']) && isset($_SESSION['password'])) $session["valid"] = true;
if (isset($_SESSION['username'])) $session["username"] = $_SESSION['username'];
if (isset($_SESSION['password'])) $session["password"] = $_SESSION['password'];

// Route passed via mod rewrite
$q = ""; if (isset($_GET['q'])) $q = $_GET['q'];
// unallowed routes
$blacklist = explode(',', 'login,logout,another-disallowed-path');

$format = "html";
$content = "";

switch ($q)
{
    // example of wrapping a page in a theme view:
    case "":
    case "feeds":
        $format = "themedhtml";
        if ($session["valid"]) {
<<<<<<< HEAD
            $content = view("views/feeds.php", array("session"=>$session));
=======
            $content = view("views/feeds.php",array("session"=>$session));
>>>>>>> 9257df7f1791e66375b0291690bd96dc14768c22
        } else {
            $content = view("views/login_view.php");
        }
        break;

    case "minimal":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/minimal.php", array("session"=>$session));
        } else {
            $content = view("views/login_view.php");
        }
        break;

    case "vuetest":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/vuetest.php", array("session"=>$session));
        } else {
            $content = view("views/login_view.php");
        }
        break;

    case "graph":
        $format = "themedhtml";
        if ($session["valid"]) {
            $content = view("views/graph.php",array("session"=>$session));
        } else {
            $content = view("views/login_view.php",array());
        }
        break;
        
    // json api route
    case "auth":
        $format = "json";
        if (isset($_POST["username"]) && isset($_POST["password"])) {

            $username = $_POST["username"];
            $password = $_POST["password"];
            $next = filter_input(INPUT_POST, "next", FILTER_SANITIZE_STRING);

            $content = json_decode(http_request("POST", "https://emoncms.org/user/auth.json", array(
                "username" => $username,
                "password" => $password,
            )));
            // pass a full url where path passed as $_POST['next']
            if (!in_array($next, $blacklist)) $content->next = getFullUrl($next);

            // TODO: check that user exists in MQTT server database here...

            if (isset($content->success) && $content->success) {
                session_regenerate_id();
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
            }
        }
	// header("Access-Control-Allow-Origin: *");
        break;

    case "logout":
        $format = "themedhtml";
        session_unset();
        session_destroy();
        $content = '<h2 class="mt-5">Logout successful</h2>';
        break;

    case "login":
        $format = "themedhtml";
        $content = view("views/login_view.php");
        break;

    default:
        $format = "themedhtml";
        $content = "<h4>Error 404</h4>Not Found";
}

switch ($format) 
{
    case "themedhtml":
        header('Content-Type: text/html');
        print view("views/theme.php", array('session'=>$session, "content"=>$content));
        break;
    case "html":
        header('Content-Type: text/html');
        print $content;
        break;
    case "text":
        header('Content-Type: text/plain');
        print $content;
        break;
    case "json":
        header('Content-Type: application/json');
        print json_encode($content);
        break;
}
