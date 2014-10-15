<?php
// OPTIONS - PLEASE CONFIGURE THESE BEFORE USE!

$yourEmail = "warrend555@gmail.com"; // the email address you wish to receive these mails through
$yourWebsite = "FlyoverBK"; // the name of your website
$thanksPage = ''; // URL to 'thanks for sending mail' page; leave empty to keep message on the same page 
$maxPoints = 4; // max points a person can hit before it refuses to submit - recommend 4
$requiredFields = "name,email,message"; // names of the fields you'd like to be required as a minimum, separate each field with a comma


// DO NOT EDIT BELOW HERE
$error_msg = array();
$result = null;

$requiredFields = explode(",", $requiredFields);

function clean($data) {
	$data = trim(stripslashes(strip_tags($data)));
	return $data;
}
function isBot() {
	$bots = array("Indy", "Blaiz", "Java", "libwww-perl", "Python", "OutfoxBot", "User-Agent", "PycURL", "AlphaServer", "T8Abot", "Syntryx", "WinHttp", "WebBandit", "nicebot", "Teoma", "alexa", "froogle", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz");

	foreach ($bots as $bot)
		if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
			return true;

	if (empty($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == " ")
		return true;
	
	return false;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (isBot() !== false)
		$error_msg[] = "No bots please! UA reported as: ".$_SERVER['HTTP_USER_AGENT'];
		
	// lets check a few things - not enough to trigger an error on their own, but worth assigning a spam score.. 
	// score quickly adds up therefore allowing genuine users with 'accidental' score through but cutting out real spam :)
	$points = (int)0;
	
	$badwords = array("adult", "beastial", "bestial", "blowjob", "clit", "cum", "cunilingus", "cunillingus", "cunnilingus", "cunt", "ejaculate", "fag", "felatio", "fellatio", "fuck", "fuk", "fuks", "gangbang", "gangbanged", "gangbangs", "hotsex", "hardcode", "jism", "jiz", "orgasim", "orgasims", "orgasm", "orgasms", "phonesex", "phuk", "phuq", "pussies", "pussy", "spunk", "xxx", "viagra", "phentermine", "tramadol", "adipex", "advai", "alprazolam", "ambien", "ambian", "amoxicillin", "antivert", "blackjack", "backgammon", "texas", "holdem", "poker", "carisoprodol", "ciara", "ciprofloxacin", "debt", "dating", "porn", "link=", "voyeur", "content-type", "bcc:", "cc:", "document.cookie", "onclick", "onload", "javascript");

	foreach ($badwords as $word)
		if (
			strpos(strtolower($_POST['comments']), $word) !== false || 
			strpos(strtolower($_POST['name']), $word) !== false
		)
			$points += 2;
	
	if (strpos($_POST['comments'], "http://") !== false || strpos($_POST['comments'], "www.") !== false)
		$points += 2;
	if (isset($_POST['nojs']))
		$points += 1;
	if (preg_match("/(<.*>)/i", $_POST['comments']))
		$points += 2;
	if (strlen($_POST['name']) < 3)
		$points += 1;
	if (strlen($_POST['comments']) < 15 || strlen($_POST['comments'] > 1500))
		$points += 2;
	if (preg_match("/[bcdfghjklmnpqrstvwxyz]{7,}/i", $_POST['comments']))
		$points += 1;
	// end score assignments

	foreach($requiredFields as $field) {
		trim($_POST[$field]);
		
		if (!isset($_POST[$field]) || empty($_POST[$field]) && array_pop($error_msg) != "Please fill in all the required fields and submit again.\r\n")
			$error_msg[] = "Please fill in all the required fields and submit again.";
	}

	if (!empty($_POST['name']) && !preg_match("/^[a-zA-Z-'\s]*$/", stripslashes($_POST['name'])))
		$error_msg[] = "The name field must not contain special characters.\r\n";
	if (!empty($_POST['email']) && !preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', strtolower($_POST['email'])))
		$error_msg[] = "That is not a valid e-mail address.\r\n";
	
	if ($error_msg == NULL && $points <= $maxPoints) {
		$subject = "Message from FlyoverBK";
		
		$message = "You received this e-mail message through your website: \n\n";
		foreach ($_POST as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $subval) {
					$message .= ucwords($key) . ": " . clean($subval) . "\r\n";
				}
			} else {
				$message .= ucwords($key) . ": " . clean($val) . "\r\n";
			}
		}
		$message .= "\r\n";
		$message .= 'IP: '.$_SERVER['REMOTE_ADDR']."\r\n";
		$message .= 'Browser: '.$_SERVER['HTTP_USER_AGENT']."\r\n";
		$message .= 'Points: '.$points;

		if (strstr($_SERVER['SERVER_SOFTWARE'], "Win")) {
			$headers   = "From: $yourEmail\r\n";
		} else {
			$headers   = "From: $yourWebsite <$yourEmail>\r\n";	
		}
		$headers  .= "Reply-To: {$_POST['email']}\r\n";

		if (mail($yourEmail,$subject,$message,$headers)) {
			if (!empty($thanksPage)) {
				header("Location: $thanksPage");
				exit;
			} else {
				$result = 'Thanks for your message We will be in touch soon!';
				$disable = true;
			}
		} else {
			$error_msg[] = 'Your mail could not be sent this time. ['.$points.']';
		}
	} else {
		if (empty($error_msg))
			$error_msg[] = 'Your mail looks too much like spam, and could not be sent this time. ['.$points.']';
	}
}
function get_data($var) {
	if (isset($_POST[$var]))
		echo htmlspecialchars($_POST[$var]);
}
?>

<!doctype html>
<html lang="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Smart and stylish website development. Zero pretension.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flyover BK | Contact Us</title>

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" sizes="192x192" href="images/touch/chrome-touch-icon-192x192.png">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Web Starter Kit">
    <link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">

    <!-- Tile icon for Win8 (144x144 + tile color) -->
    <meta name="msapplication-TileImage" content="images/touch/ms-touch-icon-144x144-precomposed.png">
    <meta name="msapplication-TileColor" content="#3372DF">

    <!-- SEO: If your mobile URL is different from the desktop URL, add a canonical link to the desktop page https://developers.google.com/webmasters/smartphone-sites/feature-phones -->
    <!--
    <link rel="canonical" href="http://www.example.com/">
    -->

    <!-- Page styles -->
    <link rel="stylesheet" href="styles/main.css">
</head>

<body>

    <header class="app-bar promote-layer bar-background">
        <div>
            <button class="menu">
                <img src="images/hamburger.svg" alt="Menu">
            </button>
            <p class="logo">Flyover</p>
        </div>

    </header>

    <nav class="navdrawer-container promote-layer">
        <h4>Navigation</h4>
        <ul>
            <li><a href="index.html">Home</a>
            </li>
            <li><a href="#hello">About Us</a>
            </li>
            <li><a href="projects.html">Projects</a>
            </li>
            <li><a href="contact-us.html">Contact Us</a>
            </li>
        </ul>
    </nav>


<main>

		<div="page-header">
			<h3 class="page-header xxlarge text-divider">Say hello</h3>
		</div>



		<div class="container g--centered">

	        <form action="<?php echo basename(__FILE__); ?>" method="post">

	            <div class="group">
	                <input type="text" name="name" id="name" value="<?php get_data("name"); ?>" required>
	                <span class="highlight"></span>
	                <span class="bar"></span>
	                <label for="name">Name</label>
	            </div>

	            <div class="group">
	                <input type="text" name="email" id="email" value="<?php get_data("email"); ?>"required>
	                <span class="highlight"></span>
	                <span class="bar"></span>
	                <label for="email">Email</label>
	            </div>

	            <div class="group">
	                <textarea name="message" id="message" required><?php get_data("message"); ?></textarea>
	                <!-- <input type="text" required> -->
	                <span class="highlight"></span>
	                <span class="bar"></span>
	                <label for="message">Message</label>
	            </div>

	            <input type="submit" name="submit" id="submit" class="button--primary" <?php if (isset($disable) && $disable === true) echo ' disabled="disabled"'; ?>>

	        </form>
	        
        </div>

<?php
if (!empty($error_msg)) {
	echo '<p class="error">ERROR: '. implode("<br />", $error_msg) . "</p>";
}
if ($result != NULL) {
	echo '<p class="success">'. $result . "</p>";
}
?>

    </main>

    <!-- build:js scripts/main.min.js -->
    <script src="scripts/main.js"></script>
    <!-- endbuild -->

    <!-- Google Analytics: change UA-XXXXX-X to be your site's ID -->
    <script>
    (function(i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function() {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    ga('create', 'UA-XXXXX-X', 'auto');
    ga('send', 'pageview');
    </script>
    <!-- Built with love -->
</body>

</html>