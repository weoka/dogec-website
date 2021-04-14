<?php
/* Error Handling */
ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(-1);
register_shutdown_function('shutdownHandler');
set_error_handler('errorHandler');

$errDev   = 2;	// 0 = Hidden, 1 = Masked, 2 = Displayed
$errLive  = 1;
$errLog   = true;
$errEmail = true;


/* Set Timezone */
date_default_timezone_set('America/New_York');


/* Minify */
if (!isset($_GET['minify'])) include('libraries/compressor.php');


/* Global Variables */
$cfgCompany  = '';
$cfgGmapKey  = '';
$cfgNavClass = '';


/* Current URL/URI */
$strPage   = getPage();				// login.php
$strUri    = getUri();				// /login/?a=expired
$strUriQs  = strtok($strUri, '?');	// /login/
$strHost   = getHost();				// http://localhost
$strUrl    = getUrl();				// http://localhost/login/?a=expired
$strUrlExt = getExt($strUriQs);		// xml


/* Save Referrer */
saveReferrer();


/* Default Mail Settings */
$mailServer   = '';
$mailUsername = '';
$mailPassword = '';
$mailError    = '';


/* Include Open Database */
if (!isset($con) && file_exists('inc_db.php')) {
	include('inc_db.php');
}


//-------------------------------------------------------------------------------------//


/* Error Handler Functions */
function errorHandler($type, $message, $file, $line) {
	global $errDev, $errLive, $errEmail, $errLog, $mailServer, $mailUsername, $mailPassword, $mailError;

	$file    = @basename($file);
	$strHost = $_SERVER['HTTP_HOST'];
	$blnDev  = ($strHost == 'localhost' || $strHost == '127.0.0.1') ? true : false;

	$arrError = Array(
		0x0001 => 'E_ERROR',
		0x0002 => 'E_WARNING',
		0x0004 => 'E_PARSE',
		0x0008 => 'E_NOTICE',
		0x0010 => 'E_CORE_ERROR',
		0x0020 => 'E_CORE_WARNING',
		0x0040 => 'E_COMPILE_ERROR',
		0x0080 => 'E_COMPILE_WARNING',
		0x0100 => 'E_USER_ERROR',
		0x0200 => 'E_USER_WARNING',
		0x0400 => 'E_USER_NOTICE',
		0x0800 => 'E_STRICT',
		0x1000 => 'E_RECOVERABLE_ERROR',
		0x2000 => 'E_DEPRECATED',
		0x4000 => 'E_USER_DEPRECATED'
	);

	if(!@is_string($strError = @array_search($type, @array_flip($arrError)))) {
		$strError = 'E_UNKNOWN';
	}

	if ($errLog && !($blnDev && $strError == 'E_CORE_WARNING')) {
		$strLine = date('m/d/Y h:i:s A') .
			"\t" . getIP() .
			"\t" . $strError .
			"\t" . $file .
			"\t" . '#' . $line .
			"\t" . $message .
			"\t" . getUrl().
			"\t" . $_SERVER['HTTP_USER_AGENT'];
		writeLog($strLine, 'errors.log');
	}

	if ($errEmail && $mailError != '' && !$blnDev) {
		require_once('libraries/useragentparser.php');
		$arrUA = parse_user_agent();

		$arrInfo = array(
			'^Error'         => 'Heading1',
			'Error Message'  => $message,
			'File Name'      => $file,
			'Line Number'    => $line,
			'Request Method' => $_SERVER['REQUEST_METHOD'],
			'Error Type'     => $strError,
			'Date & Time'    => date('m/d/Y h:i:s A'),
			'Current URL'    => getUrl(),
			'Previous URL'   => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')
		);

		if ($arrUA['platform'] != '') {
			$arrUser = array(
				'^User Details'  => 'Heading1',
				'Platform'       => $arrUA['platform'],
				'Browser'        => $arrUA['browser'] . ($arrUA['version'] != '' ? ' v' . $arrUA['version'] : ''),
				'IP Address'     => getIP()
			);
		}
		else {
			$arrUser = array(
				'^Bot Details'   => 'Heading1',
				'User Agent'     => $_SERVER['HTTP_USER_AGENT'],
				'IP Address'     => getIP()
			);
		}

		if ($_POST) {
			$arrInfo = $arrInfo + $arrUser + array('^POST Variables' => 'Heading1') + $_POST;
		}
		else {
			$arrInfo = $arrInfo + $arrUser;
		}

		mailSend(
			$arrInfo, str_replace('www.', '', $strHost), 'error@', '', $mailError, '', '', '', '',
			$message, '', '', 'images/Logo.png', 'styles/style.css', 'emails/default.html', 'HTML', 'High',
			false, true, false, $mailServer, $mailUsername, $mailPassword
		);
	}

	if ($errDev == 2 && $blnDev || $errLive == 2 && !$blnDev) {
		if ($blnDev && $strError == 'E_CORE_WARNING') {
			$strError = '';
		}
		else {
			$strError = '<pre>' . @sprintf("[ %s ] %s #%d: <b>%s</b>", $strError, $file, $line, $message) . '</pre>';
		}
	}
	else if ($errDev == 1 && $blnDev || $errLive == 1 && !$blnDev) {
		$strError = '<span style="display:inline-block; background:yellow; color:red; padding:0.5em;"><strong>An error has occurred.</strong> We have been notified of the issue. Please contact us for further assistance.</span>';
	}
	else {
		$strError = '';
	}

	return(print($strError));
}

function shutdownHandler() {
    if(@is_array($arrError = @error_get_last())) {
        return(@call_user_func_array('errorHandler', $arrError));
    }
    return(true);
}


/* Debug (Print to Screen) */
function debug($objInput, $blnExit = false, $blnTextarea = false, $blnXml = false, $blnGetError = false) {
	$strOut = '';

	if (is_array($objInput) || is_object($objInput)) {
		$strOut .= print_r($objInput, true);
	}
	else if (substr($objInput,0,1) == '{' && substr($objInput,-1,1) == '}') {
		$strOut .= print_r(json_decode($objInput, true), true);
	}
	else if (strpos($objInput,'<?xml') > -1 || $blnXml) {
		$strOut    = '';
		$blnClose  = false;
		$intIndent = -1;
		$arrElems  = explode('<', $objInput);
		foreach($arrElems as $strElem) {
			$strElem = trim($strElem);
			if (!empty($strElem)) {
				if ($strElem[0]=='/') { $blnIsClose = true; }
				else { $blnIsClose = false; }
				if(strstr($strElem, '/>')){
					$strPrefix = "\n" . str_repeat(" ",$intIndent);
					$blnTag = true;
				}
				else{
					if (!$blnClose && $blnIsClose){
						if ($blnTag){
							$intIndent--;
							$strPrefix = "\n" . str_repeat("\t", $intIndent);
						}
						else{
							$strPrefix = '';
							$intIndent--;
						}
					}
					if ($blnClose  && !$blnIsClose) { $strPrefix = "\n" . str_repeat("\t",$intIndent); $intIndent++; }
					if ($blnClose  && $blnIsClose)  { $intIndent--; $strPrefix = "\n" . str_repeat("\t",($intIndent >= 0 ? $intIndent : 0)); }
					if (!$blnClose && !$blnIsClose) { $strPrefix = ($intIndent >= 0) ? "\n" . str_repeat("\t",$intIndent) : "\n"; $intIndent++; }
					$blnTag = false;
				}
				$strOut  .= $strPrefix . '<' . $strElem;
				$blnClose = $blnIsClose;
			}
		}
		$strOut = htmlentities($strOut);
	}
	else {
		if (is_bool($objInput)) {
			$strOut .= ($objInput) ? 'true' : 'false';
		}
		else {
			$strOut .= $objInput;
		}
	}

	if ($blnTextarea) {
		//$strOut = str_replace("\t", '&#09;', $strOut);
		//$strOut = str_replace(" ", '&nbsp;', $strOut);
		$strOut = htmlentities($strOut);

		$strOut = '<pre contenteditable="true" spellcheck="false" ondblclick="document.execCommand(\'selectAll\'); document.execCommand(\'copy\');" style="    border:1px solid #CCC; background:rgba(255,255,255,0.75); padding:1em; white-space:pre;">' . $strOut . '</pre>';

		/*
		$intBrk = count(explode("\n", $strOut));
		$intRow = ($intBrk < 1) ? 1 : $intBrk - 1;
		$strOut = '<textarea style="width:100%; font-family:monospace; border:1px solid #CCC; padding:1em;" ondblclick="document.execCommand(\'selectAll\'); document.execCommand(\'copy\');" rows="' . $intRow . '" spellcheck="false">' . $strOut . '</textarea>'; //str_replace("\t", "", $strOut)
		*/
	}
	else {
		$strOut = '<pre>' . $strOut . '</pre>';
	}

	if ($blnGetError) {
		echo '<pre style="color:red;">' . print_r(error_get_last(), true) . '</pre>';
	}

	echo $strOut;
	echo str_repeat(' ', 1024*64);
	flush();

	if ($blnExit) exit();
}


/* $_GET/POST/REQUEST/COOKIE/ARRAY Function */
// req('Email', true);
// req('Email');
// req('beds', 'numeric', 'get');
// req('value', true, 'cookie', true);
// req('firstname', false, $arrRs);
function req($strName, $strClean = false, $strMethod = 'post', $strDefault = '', $blnDecrypt = false) {
	$strName = str_replace(' ', '_', $strName);

	if (is_array($strMethod)) {
		if (!empty($strMethod)) {
			$strVal = isset($strMethod[$strName]) ? $strMethod[$strName] : $strDefault;
		}
		else {
			$strVal = '';
		}
	}
	else {
		switch (strtolower($strMethod)) {
			case 'get':
				$strVal = isset($_GET[$strName]) ? $_GET[$strName] : '';
				break;

			case 'post':
				$strVal = isset($_POST[$strName]) ? $_POST[$strName] : '';
				break;

			case 'request':
				$strVal = isset($_REQUEST[$strName]) ? $_REQUEST[$strName] : '';
				break;

			case 'cookie':
				$strVal = isset($_COOKIE[$strName]) ? $_COOKIE[$strName] : '';
				break;

			case 'session':
				$strVal = isset($_SESSION[$strName]) ? $_SESSION[$strName] : '';
				break;

			default:
				$strVal = '';
				break;
		}
	}

	if ($blnDecrypt && $strVal != '') $strVal = encryptDecrypt($strVal);

	if ($strVal == '') $strVal = $strDefault;

	if ($strClean && !is_array($strVal)) $strVal = clean($strVal, $strClean);

	if (!is_array($strMethod) && strtolower($strMethod) == 'cookie') $strVal = stripslashes($strVal);

	return $strVal;
}


/* Clean XSS/JS/SQL Injection */
function clean($strValue, $strAllow = '') {
	if (is_string($strValue) || is_numeric($strValue)) {
		$strValue = trim($strValue);
		$strValue = addslashes($strValue);
		$strValue = strip_tags(htmlspecialchars($strValue));

		switch (strtolower($strAllow)) {
			case 'alphanumeric':
				$strValue = preg_replace('![^a-zA-Z0-9-.]!i', '', $strValue);
				break;

			case 'alpha':
				$strValue = preg_replace('![^a-zA-Z-.]!i', '', $strValue);
				break;

			case 'numeric':
				$strValue = preg_replace('![^0-9.]!i', '', $strValue);
				break;

			case 'date':
				$strValue = ($strValue != '') ? date('Y-m-d', strtotime($strValue)) : '';
				break;

			case 'time':
				$strValue = ($strValue != '') ? date('h:i A', strtotime($strValue)) : '';
				break;

			case 'datetime':
				$strValue = ($strValue != '') ? date('Y-m-d h:i A', strtotime($strValue)) : '';
				break;
		}

		return $strValue;
	}
	else {
		return null;
	}
}


/* Clean Unknown Characters Function */
function cleanChars($str) {
	$arrStr = str_split($str);
	$strNew = '';
	foreach ($arrStr as $strChr) {
		$intChr = ord($strChr);
		if ($intChr == 163) { $strNew .= $strChr; continue; } //Keep �
		if ($intChr > 31 && $intChr < 127) {
			$strNew .= $strChr;
		}
	}
	return $strNew;
}


/* Send Mail Functions */
function mailSend($arrPost, $strFromName = '', $strFrom = '', $strToName = '', $strTo = '', $strCC = '', $strBCC = '', $strReply = '', $strReturn = '',
	              $strSubject = '', $strTitle = '', $strMessage = '', $strLogo = '', $strCSS = '', $strBody = '', $strFormat = 'HTML', $strPriority = '',
				  $blnShowEmpty = false, $blnReferrer = false, $blnDebug = false, $strServer = '', $strUsername = '', $strPassword = '') {

	// Settings
	$strMailLibrary  = 'PHPMailer'; // PEAR, PHPMailer
	$blnMailMulti    = false;
	$strHeadChr      = '^';
	$strBullHTML     = '&bull; ';
	$strBullText     = '- ';
	$strTbl1HTML     = '<table class="table" border="0" align="center"><tbody>';
	$strTbl2HTML     = '</tbody></table>';
	$strCol1HTML     = '<td width="35%" align="left" class="col1">';
	$strCol2HTML     = '<td width="65%" align="left" class="col2">';
	$strEncoding     = '8bit';
	$strCharset      = 'UTF-8';
	$strBoundary     = '=_';
	$strCssFont      = 'Helvetica, Arial, sans-serif';
	$strCssColor     = '#646464';
	$strCssLinkColor = '#0066FF';
	$strCssH1Color   = '#000';
	$strCssH2Color   = '#999';
	$strCssH3Color   = '#CCC';
	$strCssC1Color   = $strCssColor;
	$strCssC1Back    = '#E1E1E1';
	$strCssC2Color   = $strCssColor;
	$strCssC2Back    = '#FBFBFB';
	$strCssBGClass   = '.Background1';

	// Variables
	$strEmail    = '';
	$intLenMax   = 0;
	$strFormat   = strtoupper($strFormat);
	$strDomain   = $_SERVER['HTTP_HOST'];
	$strURL      = 'http://' . $strDomain;

	if ($_POST) {
		$strFromName = (isset($_POST['_fromname'])) ? $_POST['_fromname'] : $strFromName;
		$strFrom     = (isset($_POST['_from']))     ? $_POST['_from']     : $strFrom;
		$strTo       = (isset($_POST['_to']))       ? $_POST['_to']       : $strTo;
		$strCC       = (isset($_POST['_cc']))       ? $_POST['_cc']       : $strCC;
		$strBCC      = (isset($_POST['_bcc']))      ? $_POST['_bcc']      : $strBCC;
		$strSubject  = (isset($_POST['_subject']))  ? $_POST['_subject']  : $strSubject;
		$strTitle    = (isset($_POST['_title']))    ? $_POST['_title']    : $strTitle;
		$strMessage  = (isset($_POST['_message']))  ? $_POST['_message']  : $strMessage;
		$strLogo     = (isset($_POST['_logo']))     ? $_POST['_logo']     : $strLogo;
		$strCSS      = (isset($_POST['_css']))      ? $_POST['_css']      : $strCSS;
		$strFormat   = (isset($_POST['_format']))   ? $_POST['_format']   : $strFormat;
		$strReturn   = (isset($_POST['_return']))   ? $_POST['_return']   : $strReturn;
	}

	// Clean Up
	$strSubject = strip_tags($strSubject);

	// Get Longest Key
	if (is_null($arrPost) || $arrPost == '') $arrPost = array();
	foreach ($arrPost as $strKey=>$strVal) {
		if (($blnShowEmpty == false && $strVal != '')
		 || ($blnShowEmpty == true)) {
			$intLenTmp = strlen($strKey);
			if ($intLenTmp > $intLenMax) {
				$intLenMax = $intLenTmp;
			}
		}
	}

	// Get Referrer
	$strReferrer = ($blnReferrer) ? getReferrer() : '';

	// Get Template
	if (substr($strBody, -5) == '.html'
	 || substr($strBody, -4) == '.txt') {
		//$strBody = file_get_contents(realpath($strBody));
		$strBody = file_get_contents(dirname(__FILE__) . '/' . $strBody);
	}

	// Table Rows
	$strTableHTML = '';
	$strTableText = '';
	$strHeader    = '';
	foreach ($arrPost as $strKey => $strVal) {
		$strInputs = 'submit, send, subscribe, signup, save, continue, next, finish, upload, captcha, captcheck, x, y, view_mode, recaptcha_challenge_field, recaptcha_response_field';

		if (!in_array(strtolower($strKey), explode(', ', $strInputs))
			&& substr($strKey,-2) != '_x'
			&& substr($strKey,-2) != '_y'
			&& substr($strKey,0,1) != '_'
			&& substr($strKey,0,9) != 'plupload_') {

			if (($blnShowEmpty && $strVal == '') || substr($strKey, 0, 1) == $strHeadChr || $strVal != '') {

				if (strpos(strtolower($strKey), 'email') === 0
				 || strpos(strtolower($strKey), 'e-mail') === 0) {
					$strEmail = $strVal;
				}

				if (!is_array($strVal)) {
					if (substr($strVal, 0, 1) == '<' && !strpos($strVal, '>'))
						$strVal = str_replace('<', '&lt;', $strVal);

					if (substr($strVal, 0, 1) == '>' && !strpos($strVal, '<'))
						$strVal = str_replace('>', '&gt;', $strVal);
				}

				$strLabel  = str_replace('_', ' ', $strKey);
				$intLenTmp = strlen($strLabel);
				$intLenDif = $intLenMax > $intLenTmp ? $intLenMax - $intLenTmp : 0;
				$strSpace  = str_repeat(' ', $intLenDif);

				if (substr($strLabel, 0, 1) == $strHeadChr) {
					$strHeader = substr($strLabel, 1);

					// HTML
					if ($strTableHTML != '') {
						$strTableHTML .= '<tr><td colspan="2">&nbsp;</td></tr>';
					}

					$strTableHTML .= '
					<tr>
						<td colspan="2"><h2 class="no-margin">' . $strHeader . '</h2></td>
					</tr>';

					// Text
					$strTableText .= PHP_EOL . '[ ' . $strHeader . ' ]' . PHP_EOL;
				}
				else {
					if ($strHeader != '') {
						if (strlen($strLabel) > strlen($strHeader) && substr($strLabel, 0, strlen($strHeader)) == $strHeader) {
							$strLabel = substr($strLabel, strlen($strHeader) + 1);
						}
					}

					// HTML
					$strTableHTML .= '
					<tr>' . $strCol1HTML . $strLabel . '</td>' . $strCol2HTML;

					if (is_array($strVal)) {
						//$strTableHTML .= $strBullHTML . implode($strVal, '<br>' . $strBullHTML);
						$strTableHTML .= '<ul>';
						foreach($strVal as $strItem) {
							$strTableHTML .= '<li>' . nl2br($strItem) . '</li>';
						}
						$strTableHTML .= '</ul>';
					}
					else {
						$strTableHTML .= nl2br($strVal);
					}

					$strTableHTML .= '</td></tr>';

					// Text
					$strTableText .= $strLabel . ': ' . $strSpace;

					if (is_array($strVal)) {
						$strTableText .= $strBullText . implode($strVal, PHP_EOL .
										 str_repeat(' ', $intLenMax + 2) . $strBullText);
					}
					else {
						$strTableText .= $strVal;
					}

					if ($strLabel != '') $strTableText .= PHP_EOL;
				}
			}
		}
	}

	// Placeholders
	$strSubject  = mailPopulate($strSubject);
	$strTitle    = mailPopulate($strTitle);
	$strFromName = mailPopulate($strFromName);
	$strBody     = mailPreload($strBody,
		array(
			'from-name' => $strFromName,
			'from'      => $strFrom,
			'to'        => $strTo,
			'cc'        => $strCC,
			'bcc'       => $strBCC,
			'subject'   => $strSubject,
			'title'     => $strTitle,
			'domain'    => $strDomain,
			'url'       => $strURL
		)
	);

	// HTML Format
	if ($strFormat == 'HTML') {
		if ($strCSS != '') {
			require_once('libraries/cssparser.php');
			$objCSS = new cssparser;

			if (substr($strCSS, -4) == '.css') {
				$objCSS->Parse(dirname(__FILE__) . '/' . $strCSS);
			}
			else {
				$objCSS->ParseStr($strCSS);
			}

			$arrCSS = $objCSS->css;

			$defCssC1Color   = $strCssC1Color;
			$defCssC1Back    = $strCssC1Back;

			if (isset($arrCSS['body']))			$strCssFont      = mailCSS($arrCSS['body']['font-family'], $strCssFont);
			if (isset($arrCSS['body']))			$strCssColor     = mailCSS($arrCSS['body']['color'], $strCssColor);
			if (isset($arrCSS['a']))			$strCssLinkColor = mailCSS($arrCSS['a']['color'], $strCssLinkColor);
			if (isset($arrCSS['h1']))			$strCssH1Color   = mailCSS($arrCSS['h1']['color'], $strCssH1Color);
			if (isset($arrCSS['h2']))			$strCssH2Color   = mailCSS($arrCSS['h2']['color'], $strCssH2Color);
			if (isset($arrCSS['h3']))			$strCssH3Color   = mailCSS($arrCSS['h3']['color'], $strCssH3Color);
			if (isset($arrCSS[$strCssBGClass]))	$strCssC1Color   = mailCSS($arrCSS[$strCssBGClass]['color'], $strCssC1Color, true);
			if (isset($arrCSS[$strCssBGClass]))	$strCssC1Back    = mailCSS($arrCSS[$strCssBGClass]['background-color'], $strCssC1Back);

			if (colorLumDiff($defCssC1Back, $strCssC1Back) < 1) {
				//$strCssC1Color = $defCssC1Color;
				$strCssC1Back  = $defCssC1Back;
			}
		}

		if ($strMessage != '') {
			$strMessage = '<p>' . $strMessage . '</p>';
		}

		if ($strReferrer != '') {
			$strTableHTML .= '<tr>' . $strCol1HTML . 'Referrer</td>' . $strCol2HTML . $strReferrer . '</td></tr>';
		}

		$strBodyHTML = asciiToHTML($strBody);
		$strBodyHTML = mailPreload($strBodyHTML,
			array(
				'css-font'       => $strCssFont,
				'css-color'      => $strCssColor,
				'css-link-color' => $strCssLinkColor,
				'css-h1-color'   => $strCssH1Color,
				'css-h2-color'   => $strCssH2Color,
				'css-h3-color'   => $strCssH3Color,
				'css-c1-color'   => $strCssC1Color,
				'css-c1-back'    => $strCssC1Back,
				'message'        => $strMessage,
				'logo'           => str_replace(' ', '%20', $strLogo)
			)
		);

		$strBodyHTML = str_replace('src="', 'src="' . $strURL . '/', $strBodyHTML);
	}

	// Text Format
	$strBodyText = str_replace('& ', htmlentities('& '), $strBodyHTML);

	require_once('libraries/html2text.php');
	$strBodyText = Html2Text\Html2Text::convert($strBodyText);

	if ($strReferrer != '') {
		$strLabel    = 'Referrer';
		$intLenTmp   = strlen($strLabel);
		$intLenDif   = $intLenMax > $intLenTmp ? $intLenMax - $intLenTmp : 0;
		$strSpace    = str_repeat(' ', $intLenDif);
		$strReferrer = str_replace('Landing:', 'Landing: ',
			           str_replace('<br />', PHP_EOL,
			           strip_tags($strReferrer, '<br>')));

		$strTableText .= $strLabel . ': ' . $strSpace . $strReferrer;
	}

	// Format Body
	if ($strBodyHTML != '') {
		$strBodyHTML = mailPreload($strBodyHTML, array('table' => $strTbl1HTML . $strTableHTML . $strTbl2HTML));

		require_once('libraries/emogrifier.php');
		$objEmogrifier = new \Pelago\Emogrifier($strBodyHTML);
		$strBodyHTML   = $objEmogrifier->emogrify();
		$strBodyHTML   = str_replace("\t", "", $strBodyHTML);
		$strBodyHTML   = str_replace(' ', '', $strBodyHTML);
	}

	if ($strBodyText != '') {
		$strBodyText = mailPreload($strBodyText, array('table' => PHP_EOL . $strTableText));
	}

	// Setup Mail
	$strFrom  = mailFormat($strFrom);
	$strTo    = mailFormat($strTo);
	$strCC    = mailFormat($strCC);
	$strBCC   = mailFormat($strBCC);
	$strReply = mailFormat($strReply);

	if ($strSubject == '') {
		$strSubject = $strTitle;
		if ($strFromName != '') {
			$strSubject .= ' | ' . $strFromName;
		}
	}

	if ($strServer == '') {

		// PHP Mail
		if ($strFrom != '' && $strFromName != '') {
			$strFrom = '"' . $strFromName . '" <' . $strFrom . '>';
		}

		if ($strTo != '' && $strToName != '') {
			$strTo = '"' . $strToName . '" <' . $strTo . '>';
		}

		switch (strtolower($strPriority)) {
			case 'high':
				$strPriority  = 'High';
				$strXPriority = '1 (Highest)';
				break;

			case 'low':
				$strPriority  = 'Low';
				$strXPriority = '5 (Lowest)';
				break;

			default:
				$strPriority  = '';
				$strXPriority = '';
				break;
		}

		$strBoundary = uniqid($strBoundary);
		//$strBodyText = wordwrap($strBodyText, 80);
		//$strBodyHTML = wordwrap($strBodyHTML, 80);
		$strBody     = '';
		$strHeaders  = '';

		$strHeaders .= 'MIME-Version: 1.0' . PHP_EOL;
		$strHeaders .= 'Date: ' . date('r') . PHP_EOL;

		$strHeaders .= 'From: ' . $strFrom . PHP_EOL;
		$strHeaders .= 'Reply-To: ' . ($strReply != '' ? $strReply : $strFrom) . PHP_EOL;
		if ($strReturn != '') $strReturn  .= 'Return-Path: ' . $strReturn . PHP_EOL;
		if ($strCC != '')     $strHeaders .= 'Cc: ' . $strCC . PHP_EOL;
		if ($strBCC != '')    $strHeaders .= 'Bcc: ' . $strBCC . PHP_EOL;

		if ($strPriority != '') {
			$strHeaders .= 'X-Priority: ' . $strXPriority . PHP_EOL;
			$strHeaders .= 'X-MSMail-Priority: ' . $strPriority . PHP_EOL;
			$strHeaders .= 'Importance: ' . $strPriority . PHP_EOL;
		}

		if ($strFormat == 'HTML') {
			if ($blnMailMulti) $strHeaders .= 'Content-Type: multipart/alternative; boundary=' . $strBoundary . PHP_EOL;

			$strHeaders .= 'Content-Transfer-Encoding: ' . $strEncoding . PHP_EOL;

			if ($blnMailMulti) {
				$strBody .= PHP_EOL . "--" . $strBoundary . PHP_EOL;
				$strBody .= "Content-Type: text/plain; charset=" . $strCharset . PHP_EOL;
				$strBody .= $strBodyText;

				$strBody .= PHP_EOL . "--" . $strBoundary . PHP_EOL;
				$strBody .= "Content-Type: text/html; charset=" . $strCharset . PHP_EOL;
				$strBody .= $strBodyHTML;
				$strBody .= PHP_EOL . "--" . $strBoundary . "--";
			}
			else {
				$strHeaders .= "Content-Type: text/html; charset=" . $strCharset . PHP_EOL;
				$strBody    .= $strBodyHTML;
			}
		}
		else {
			$strHeaders .= 'Content-Type: text/plain;' . PHP_EOL;
			$strBody    .= $strBodyText;
		}

		//debug($strBody,1,1);

		// Debug to Page
		mailDebug($blnDebug, $strHeaders, $strFormat, $strBody, $strSubject, $strTo, $strFrom);

		if ($blnDebug === false) mail($strTo, $strSubject, $strBody, $strHeaders);

	}
	else {

		// SMTP Authentication
		$blnSMTP   = ($strUsername != '' && $strPassword != '') ? true : false;
		$arrServer = explode(':', $strServer);
		if (count($arrServer) > 1) {
			$strServer = $arrServer[0];
			$strPort   = $arrServer[1];
		}
		else {
			$strPort   = '25';
		}

		switch ($strMailLibrary) {
			case 'PEAR':
				require_once('Mail.php');

				if ($strFrom != '' && $strFromName != '') {
					$strFrom = '"' . $strFromName . '" <' . $strFrom . '>';
				}

				if ($strTo != '' && $strToName != '') {
					$strTo = '"' . $strToName . '" <' . $strTo . '>';
				}

				$strRecipients = $strTo;
				if ($strCC != '')  $strRecipients .= ', ' . $strCC;
				if ($strBCC != '') $strRecipients .= ', ' . $strBCC;

				$arrHeaders = array(
					'Date'         => date('r'),
					'From'         => $strFrom,
					'To'           => $strTo,
					'Cc'           => $strCC,
					'Reply-To'     => ($strReply != '' ? $strReply : $strFrom),
					'Return-Path'  => $strReturn,
					'Subject'      => $strSubject,
				);

				if ($strFormat == 'HTML') {
					require_once('Mail/mime.php');

					$arrHeaders['Content-Type'] = 'text/html; charset=' . $strCharset;

					$arrMIME = array(
						'text_encoding' => $strEncoding,
						'text_charset'  => $strCharset,
						'html_charset'  => $strCharset,
						'head_charset'  => $strCharset
					);

					$objMIME = new Mail_mime();
					$objMIME->setTXTBody($strBodyText);
					$objMIME->setHTMLBody($strBodyHTML);

					$strBody    = $objMIME->get($arrMIME);
					$arrHeaders = $objMIME->headers($arrHeaders);
				}

				$arrSMTP = array(
					'host'     => $strServer,
					'port'     => $strPort,
					'auth'     => $blnSMTP,
					'username' => $strUsername,
					'password' => $strPassword
				);

				// Debug to Page
				mailDebug($blnDebug, $arrHeaders, $strFormat, $strBody, $strSubject, $strTo, $strFrom, $strBCC, $arrSMTP);

				if ($blnDebug === false) {
					$arrMail = @Mail::factory('smtp', $arrSMTP);
					$objMail = @$arrMail->send($strRecipients, $arrHeaders, $strBody);

					if (@PEAR::isError($objMail)) {
						debug($objMail->getMessage());
					}
				}
				break;

			case 'PHPMailer':
				//require_once('libraries/phpmailer/PHPMailerAutoload.php');
				require_once('libraries/phpmailer/class.phpmailer.php');
				require_once('libraries/phpmailer/class.smtp.php');

				switch (strtolower($strPriority)) {
					case 'high':
						$intPriority = 1;
						break;

					case 'low':
						$intPriority = 5;
						break;

					default:
						$intPriority = 0;
						break;
				}

				$objMail = new PHPMailer();

				$objMail->isHTML(true);
				$objMail->CharSet    = 'text/html; charset=' . $strCharset . ';';
				//$objMail->WordWrap   = 80;

				if ($blnSMTP) {
					$objMail->isSMTP();
					$objMail->SMTPAuth   = $blnSMTP;
					$objMail->SMTPSecure = ($strPort == '25' || $strPort == '587' ? false : 'ssl'); // none, tls, ssl
					$objMail->Host       = $strServer;
					$objMail->Port       = $strPort;
					$objMail->Username   = $strUsername;
					$objMail->Password   = $strPassword;
				}

				$objMail->ReturnPath = $strReturn;
				$objMail->setFrom($strFrom, $strFromName);
				$objMail->addReplyTo(($strReply != '' ? $strReply : $strFrom));

				$arrTo  = explode(', ', $strTo);
				$arrCC  = explode(', ', $strCC);
				$arrBCC = explode(', ', $strBCC);
				foreach ($arrTo as $strEmail)  $objMail->addAddress($strEmail, $strToName);
				foreach ($arrCC as $strEmail)  $objMail->addCC($strEmail);
				foreach ($arrBCC as $strEmail) $objMail->addBCC($strEmail);

				$objMail->Subject = $strSubject;
				$objMail->Body    = $strBodyHTML;
				$objMail->AltBody = $strBodyText;

				$strHeaders = $objMail->createHeader();
				$strBody    = $objMail->createBody();

				if ($intPriority > 0) $objMail->Priority = $intPriority;

				//debug($strBodyHTML,1,1);

				// Debug to Page
				//$objMail->SMTPDebug   = 2; // 1-4
				//$objMail->Debugoutput = function($strDebug) { debug($strDebug); };
				mailDebug($blnDebug, $strHeaders, $strFormat, array($strBodyHTML, $strBodyText), $strSubject, $strTo, $strFrom, $strBCC);

				if ($blnDebug === false) {
					if(!$objMail->send()) {
						debug('<strong>ERROR:</strong> ' . $objMail->ErrorInfo, 0);
					}
				}
				break;
		}
	}
}

function mailPreload($strInput, $arrNVP) {
	if (substr($strInput, -5) == '.html'
	 || substr($strInput, -4) == '.txt') {
		$strOutput = file_get_contents(realpath($strInput));
	}
	else {
		$strOutput = $strInput;
	}

	foreach ($arrNVP as $strName => $strValue) {
		$strOutput = str_replace('{' . $strName . '}', $strValue, $strOutput);
	}

	return $strOutput;
}

function mailCSS($strNew, $strDefault, $blnAllowWhite = false) {
	if (isset($strNew)
		&& ((strtoupper($strNew) != '#FFF' && strtoupper($strNew) != '#FFFFFF') || $blnAllowWhite)
	) {
		return str_replace('"', '\'', $strNew);
	}
	else {
		return $strDefault;
	}
}

function mailFormat($strEmail) {
	$strEmail = mailPopulate($strEmail);
	$strEmail = str_replace(';', ',', $strEmail);
	$strEmail = str_replace(' ', '', $strEmail);
	$strEmail = str_replace(',', ', ', $strEmail);
	$strEmail = trim($strEmail);

	if (substr($strEmail,-1) == '@') {
		$strEmail .= preg_replace('/^(.*\.)?([^.]*\..*)$/', '$2', $_SERVER['HTTP_HOST']);
	}

	return $strEmail;
}

function mailPopulate($strText) {
	preg_match_all('!\{(.*?)\}!i', $strText, $arrMatches);

	foreach ($arrMatches[1] as $strMatch) {
		$strPost = str_replace(' ', '_', $strMatch);
		$strText = str_replace('{' . $strMatch . '}', (isset($_POST[$strPost]) ? $_POST[$strPost] : $strMatch), $strText);
	}

	return $strText;
}

function mailLoad(
	$arrPost, $strTitle, $strMessage, $strLogo, $strCSS, $strFormat, $strPriority,
	$strFromName, $strFrom, $strToName, $strTo, $strCC, $strBCC, $strReply, $strReturn,
	$strSubject, $strBody, $strServer, $strUsername, $strPassword, $strCaptcha,
	$blnReset, $blnShowEmpty, $blnReferrer, $blnDebug, $blnSend = true,
	$strFilter = 'seo, search engine, organic traffic, ppc, targeted visitors, keyword, keywords, keyphrase, keyphrases, free trial, target market',
	$strCaptchaName = 'captcheck', $strCaptchaText = '<em>I am <u>not</u> a spammer.</em>'
) {
	$strCaptcha = strtolower($strCaptcha);
	if ($strCaptcha == 'image') session_start();
	if ($strCaptchaName != 'captcheck') $strCaptchaName = '_' . $strCaptchaName;

	$arrReturn       = array();
	$strCaptchaCheck = 'display:none;';
	$strCaptchaImage = 'display:none;';
	$strCaptchaImg   = '';
	$strCaptchaJs    = '';
	$strReset        = ($blnReset) ? '' : 'display:none;';
	$blnForm         = true;
	$strPage         = $_SERVER['REQUEST_URI'];
	$strDomain       = $_SERVER['HTTP_HOST'];
	$strReferrer     = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	$blnBypass       = (strpos($strReferrer, $strDomain) && !strpos($strReferrer, $strPage)) ? true : false;

	if ($arrPost) {
		switch ($strCaptcha) {
			case 'image':
				if (isset($_SESSION['captcha']['code']) && isset($_REQUEST['captcha'])
					&& strtolower($_REQUEST['captcha']) === strtolower($_SESSION['captcha']['code'])) {
					$blnForm = false;
					session_unset();
					session_destroy();
					session_write_close();
					setcookie(session_name(),'',0,'/');
					session_regenerate_id(true);
				}
				else {
					$strCaptchaJs = "$.alerts.dialogClass = 'confirm';
					jAlert('Please re-enter the security code and try again...', 'Invalid Security Code', function(){window.history.back()});";
				}
				break;

			case 'checkbox':
				$strCaptchaTest = str_replace(' ', '_', $strCaptchaName);
				if (isset($_REQUEST[$strCaptchaTest]) && $_REQUEST[$strCaptchaTest] == $strCaptchaName) {
					$blnForm = false;
				}
				break;

			default:
				$blnForm = false;
				break;
		}

		// Bypass CAPTCHA if POSTed from same Domain, but different Page
		if ($blnBypass) $blnForm = false;

		// Filter out posts that contain bad/spam keywords
		if ($strFilter != '') {
			foreach($arrPost as $arrSub){
				if (is_array($arrSub)) {
					foreach($arrSub as $val){
						$arrNew[] = $val;
					}
				}
				else {
					$arrNew[] = $arrSub;
				}
			}
			$strPost = implode(' ', $arrNew);

			$strFilter = str_replace(', ', '|', $strFilter);
			if (preg_match('/(' . $strFilter . ')/i', $strPost)) {
				$blnSend = false;
			}
		}

		// Filter out posts that contain duplicate field values
		foreach($arrPost as $arrSub){
			if (is_array($arrSub)) {
				foreach($arrSub as $val){
					$arrDupe[] = trim($val);
				}
			}
			else {
				$arrDupe[] = trim($arrSub);
			}
		}

		$arrUnique = array_unique($arrDupe);
		$arrDupes  = array_diff_key($arrDupe, $arrUnique);

		if (count($arrDupes) > 1) {
			$blnSend = false;
		}

		// Send Email
		if (!$blnForm && $blnSend) {
			mailSend($arrPost, $strFromName, $strFrom, $strToName, $strTo, $strCC, $strBCC, $strReply, $strReturn,
					 $strSubject, $strTitle, $strMessage, $strLogo, $strCSS, $strBody, $strFormat, $strPriority,
					 $blnShowEmpty, $blnReferrer, $blnDebug, $strServer, $strUsername, $strPassword);
		}

		if (!$blnSend) $blnForm = true;
	}
	else {
		switch ($strCaptcha) {
			case 'image':
				$arrCSS = parseCSS($strCSS);

				$strText   = $arrCSS['.Background1']['color'];
				$strShadow = $arrCSS['.Background1']['text-shadow'];
				preg_match('!rgba\((.*?), (.*?), (.*?), .*\)!i', $strShadow, $arrShadow);
				$strShadow = colorRGBHex($arrShadow[1], $arrShadow[2], $arrShadow[3]);

				include('libraries/captcha/captcha.php');
				$_SESSION = array();
				$_SESSION['captcha'] = captcha(array(
					'code'            => '',
					'min_length'      => 5,
					'max_length'      => 5,
					'png_backgrounds' => array('clear.png'),
					'fonts'           => array('times_new_yorker.ttf'),
					'characters'      => 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz023456789',
					'min_font_size'   => 18,
					'max_font_size'   => 20,
					'color'           => $strText,
					'angle_min'       => 0,
					'angle_max'       => 3,
					'shadow'          => true,
					'shadow_color'    => $strShadow,
					'shadow_offset_x' => -1,
					'shadow_offset_y' => 2
				));

				$strCaptchaImage = '';
				$strCaptchaImg   = $_SESSION['captcha']['image_src'];

				$strCaptchaJs = "
				var objRow   = $('form input:submit').parents('tr');
				var strClass = objRow.prev('tr').children('td').attr('class');
				objRow.before(
					'<tr><td class=\"' + strClass + '\">Security Code:<br />' +
					'<img src=\"" . $strCaptchaImg . "\" text=\"Enter CAPTCHA Security Code\" /></td>' +
					'<td><input name=\"captcha\" class=\"required\" type=\"text\" id=\"captcha\" size=\"6\" ' +
					'maxlength=\"5\" autocomplete=\"off\" text=\"Enter CAPTCHA Security Code\" /></td></tr>'
				);";
				break;

			case 'checkbox':
				$strCaptchaCheck = '';
				$strCaptchaJs = "
				var objRow   = $('form input:submit').parents('tr');
				var strClass = ''; //objRow.prev('tr').children('td').attr('class');
				objRow.before(
					'<tr><td class=\"' + strClass + '\">&nbsp;</td>' +
					'<td><input name=\"" . $strCaptchaName . "\" type=\"checkbox\" id=\"" . $strCaptchaName . "\" value=\"" . $strCaptchaName . "\" class=\"required\"> ' +
					'<label for=\"" . $strCaptchaName . "\">" . addslashes($strCaptchaText) . "</label></td></tr>'
				);";
				break;
		}
	}

	$arrReturn['captcha'] = $strCaptchaJs;
	$arrReturn['reset']   = $strReset;
	$arrReturn['form']    = $blnForm;

	return $arrReturn;
}

function mailDebug($blnDebug, $strHeaders, $strFormat, $strBody, $strSubject, $strTo, $strFrom, $strBCC = '', $arrSMTP = null) {
	if ($blnDebug) {
		echo '<div style="padding:5px 20px 20px 20px; text-align:left;">';

		if (!is_null($arrSMTP)) debug($arrSMTP);

		if (is_array($strHeaders)) {
			debug($strHeaders);
			debug(
				'From: ' . htmlentities($strFrom) .
				($strBCC != '' ? '<br />Bcc:  ' . htmlentities($strBCC) : '')
			);
		}
		else {
			debug(htmlentities($strHeaders));
			if (!strpos($strHeaders, 'Subject:')) {
				 debug('To: ' . htmlentities($strTo) . '<br /><br /><strong>' .
				       'Subject: ' . $strSubject . '</strong>');
			}
		}

		if ($strFormat == 'HTML') {
			$strBound = '--=_';

			if (is_array($strBody)) {
				echo $strBody[0];
				debug($strBody[1], 0, 1);
			}
			else if (strpos($strBody, $strBound)) {
				$arrBody  = explode($strBound, $strBody);
				$arrBody  = array_reverse($arrBody);

				foreach ($arrBody as $strPart) {
					if (trim($strPart) != '') {
						$strHead = substr($strPart, strpos($strPart, 'Content-Type'));
						$strHead = substr($strHead, 0, strpos($strHead, PHP_EOL));

						if ($strHead != '') {
							$strBody = substr($strPart, strpos($strPart, $strHead) + strlen($strHead));
							echo '<pre style="background:#c0c0c0; border:1px solid #808080; color:#808080; padding:5px; text-align:center;">' . $strHead . '</pre>';
						}

						if (strpos($strPart, 'text/html')) {
							echo $strBody;
						}
						else if (strpos($strPart, 'text/plain')) {
							debug($strBody, 0, 1);
						}
					}
				}
			}
			else {
				echo $strBody;
			}
		}
		else {
			debug($strBody);
		}

		echo '</div>';

		if (is_bool($blnDebug)) exit();
	}
}


/* Parse CSS Function */
//ex. $arrCSS = parseCSS('styles/style.css');
//    echo $arrCSS['.Background1']['background-color'];
function parseCSS($file){
	$css = file_get_contents($file);
    preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $css, $arr);
    $result = array();
    foreach ($arr[0] as $i => $x){
        $selector = trim($arr[1][$i]);
        $rules = explode(';', trim($arr[2][$i]));
        $rules_arr = array();
        foreach ($rules as $strRule){
            if (!empty($strRule)){
                $rule = explode(":", $strRule);
                $rules_arr[trim($rule[0])] = trim($rule[1]);
            }
        }

        $selectors = explode(',', trim($selector));
        foreach ($selectors as $strSel){
            $result[$strSel] = $rules_arr;
        }
    }
    return $result;
}


/* Color Functions */
function colorHexRGB($hex) {
	$hex = str_replace('#', '', $hex);
	preg_match("/#{0,1}([0-9a-f]{1,6})$/i",$hex,$match);

	if(!isset($match[1])) {return false;}
	if(strlen($match[1]) == 6) {
		list($r, $g, $b) = array($hex[0].$hex[1],$hex[2].$hex[3],$hex[4].$hex[5]);
	}
	elseif(strlen($match[1]) == 3) {
		list($r, $g, $b) = array($hex[0].$hex[0],$hex[1].$hex[1],$hex[2].$hex[2]);
	}
	else if(strlen($match[1]) == 2) {
		list($r, $g, $b) = array($hex[0].$hex[1],$hex[0].$hex[1],$hex[0].$hex[1]);
	}
	else if(strlen($match[1]) == 1) {
		list($r, $g, $b) = array($hex.$hex,$hex.$hex,$hex.$hex);
	}
	else {
		return false;
	}
	$color = array();
	$color['r'] = hexdec($r);
	$color['g'] = hexdec($g);
	$color['b'] = hexdec($b);

	return $color;
}

function colorRGBHex($r, $g, $b) {
	$hex = '#';
	$hex.= str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
	$hex.= str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
	$hex.= str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

	return $hex;
}

function colorDiff($hex1, $hex2) { // > 500 for Readability
	$a1 = colorHexRGB($hex1);
	$a2 = colorHexRGB($hex2);

    return max($a1['r'], $a2['r']) - min($a1['r'], $a2['r']) +
           max($a1['g'], $a2['g']) - min($a1['g'], $a2['g']) +
           max($a1['b'], $a2['b']) - min($a1['b'], $a2['b']);
}

function colorBrightDiff($hex1, $hex2) { // > 125 for Readability
	$a1 = colorHexRGB($hex1);
	$a2 = colorHexRGB($hex2);

    $b1 = (299 * $a1['r'] + 587 * $a1['g'] + 114 * $a1['b']) / 1000;
    $b2 = (299 * $a2['r'] + 587 * $a2['g'] + 114 * $a2['b']) / 1000;

    return abs($b1 - $b2);
}

function colorLumDiff($hex1, $hex2) { // > 5 for Readability
	$a1 = colorHexRGB($hex1);
	$a2 = colorHexRGB($hex2);

    $l1 = 0.2126 * pow($a1['r'] / 255, 2.2) +
          0.7152 * pow($a1['g'] / 255, 2.2) +
          0.0722 * pow($a1['b'] / 255, 2.2);

    $l2 = 0.2126 * pow($a2['r'] / 255, 2.2) +
          0.7152 * pow($a2['g'] / 255, 2.2) +
          0.0722 * pow($a2['b'] / 255, 2.2);

    if ($l1 > $l2) {
        return ($l1 + 0.05) / ($l2 + 0.05);
    }
	else {
        return ($l2 + 0.05) / ($l1 + 0.05);
    }
}


/* ASCII to HTML Function */
function asciiToHTML($strInput) {
	$arrTable = array(
		"�" => "&quot;",
		"�" => "&quot;",
		"�" => "&copy;",
		"�" => "&reg;",
		"�" => "&trade;",
		"�" => "&deg;",
		"�" => "&hellip;",
		"�" => "&ndash;",
		"�" => "&mdash;",
		"�" => "&laquo;",
		"�" => "&raquo;",
		"�" => "&middot;",
		"�" => "&bull;",
		"�" => "'",
		"�" => "'",
		"�" => "x"
	);
	return strtr($strInput, $arrTable);
}


/* Trim String Function */
function trimString($strString, $intMax, $strTrail = '&#133;') {
	$strString = strip_tags($strString);
	$strString = str_replace(PHP_EOL, ' ', $strString);
	$strString = str_replace("\t", ' ', $strString);
	$strString = str_replace('   ', ' ', $strString);
	$strString = str_replace('  ', ' ', $strString);
	$strString = trim($strString);

	if (strlen($strString) > $intMax) {
		$strString = substr($strString, 0, $intMax);
		$intPos    = strrpos($strString, ' ');
		if($intPos === false) {
			return substr($strString, 0, $intMax) . $strTrail;
		}
		return substr($strString, 0, $intPos) . $strTrail;
	}
	else {
		return $strString;
	}
}


/* Current Page */
function getPage() {
	$strPage = $_SERVER['PHP_SELF'];
	if ($strPage != '') {
		return strtolower(substr($strPage,strrpos($strPage,'/')+1));
	}
	else {
		return '';
	}
}


/* Base URL */
function getBase() {
	$strUrl  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http');
	$strUrl .= '://' . $_SERVER['HTTP_HOST'] . '/';
	$strUrl .= substr($_SERVER['PHP_SELF'], 1, strrpos($_SERVER['PHP_SELF'], '/'));
	return $strUrl;
}


/* Get REQUEST_URI (or REWRITE_URL) */
function getUri() {
	$strUri = $_SERVER['REQUEST_URI'];
	$strUrl = isset($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] : '';
	if (empty($strUri)) $strUri = $strUrl;
	return $strUri;
}


/* Get Full URL */
function getUrl() {
	$strUrl = getBase();
	if (substr($strUrl, -1) == '/')
		$strUrl = substr($strUrl, 0, -1);
	$strUrl .= getUri();
	return $strUrl;
}


/* Get Host */
function getHost() {
	return (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
}


/* Get Extension */
function getExt($str) {
	return strtolower(substr(strrchr($str, '.'), 1));
}


/* HTTPS/SSL Check */
function is_https() {
	if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
		return true;
	}
	else {
		return false;
	}
}


/* Get IP Address */
function getIP() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$strIP = $_SERVER['HTTP_CLIENT_IP']; // Shared
	}
	else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$strIP = $_SERVER['HTTP_X_FORWARDED_FOR']; // Proxy
	}
	else {
		$strIP = $_SERVER['REMOTE_ADDR']; // Standard
	}

	if ($strIP == '::1') $strIP = '127.0.0.1';

	return $strIP;
}


/* Navigation Selection (Highlight Button) */
//  <li><a href="index.php" class="home<.php echo navSel($strPage, 'index, default').>">Home</a></li>
//  <li><a href="service.php" class="<.php echo navSel($strPage, 'conditions/, condition/').>">Home</a></li>
function navSel($strPageA, $strPageB, $strClass = 'active') {
	$strPgA = strtolower($strPageA);
	$strPgB = strtolower($strPageB);
	$arrPgB = explode(', ',$strPgB);
	$strOut = '';

	if ($strPgA == '') {
		$strUri = getUri();

		if (substr($strPgB, 0, 1) != '/') $strPgB = '/' . $strPgB;

		if (strpos($strUri, $strPgB) === 0) {
			$strOut = ' ' . $strClass;
		}
		else {
			$strOut = '';
		}
	}
	else if (substr($strPageB, -1) == '/') {
		$strUri = getUri();

		foreach ($arrPgB as $strPgB) {
			$strPgB = substr($strPgB, 0, 1) == '/' ? substr($strPgB, 1) : $strPgB;
			$intPos = strpos($strUri, $strPgB);
			if ($intPos == 1) {
				$strOut = ' ' . $strClass;
				break;
			}
			else {
				$strOut = '';
			}
		}
		unset($strPgB);
	}
	else {
		foreach ($arrPgB as $strPgB) {
			$intPgB = strlen($strPgB);
			if ((substr($strPgA,0,$intPgB) == $strPgB && $strPgB != '')
			|| ($strPgA == '' && $strPgB == '')) {
				$strOut = ' ' . $strClass;
				break;
			}
			else {
				$strOut = '';
			}
		}
		unset($strPgB);
	}

	return $strOut;
}


/* Build Image List from Directory */
//  echo imageList('images/gallery/',true,'c',0,0,100,75,true,'');
function imageList($strDir, $blnCaption=false, $strPos='c', $intFullW=0, $intFullH=0, $intThumbW=0, $intThumbH=0, $blnAlbums=false, $blnManualThumbs=false, $strLink='') {
	$strQuery   = 'album';
	$strList    = '';

	$strAlbum   = isset($_GET[$strQuery]) ? $_GET[$strQuery] : '';
	if ($strAlbum != '' && $blnAlbums) {
		$strDir = $strDir . $strAlbum . '/';
	}

	if (substr($strDir,0,1) == '/') {
		$strAbs = 'http://' . $_SERVER['HTTP_HOST'] . $strDir;
		$strDir = $_SERVER['DOCUMENT_ROOT'] . $strDir;
	}
	else {
		$strAbs = $strDir;
	}

	if (file_exists($strDir) && is_dir($strDir)) {
		$objDir     = opendir($strDir);
		$arrFiles   = array();
		$arrFolders = array();

		while (false !== ($strFile = readdir($objDir))) {
			if (preg_match('(jpg|jpeg|gif|png)',strtolower($strFile))) {
				$arrFiles[] = $strFile;
			}
			else if ($strFile != '.' && $strFile != '..' && is_dir($strDir . $strFile)) {
				$arrFolders[] = $strFile;
			}
		}

		natcasesort($arrFolders);

		if (count($arrFiles) == 0 && count($arrFolders) > 0 && $blnAlbums && $strAlbum == '') {
			$strAbs .= reset($arrFolders) . '/';
			$strDir = $strDir . reset($arrFolders) . '/';
			$objDir = opendir($strDir);
			unset($arrFiles);
			while (false !== ($strFile = readdir($objDir))) {
				if (preg_match('(jpg|jpeg|gif|png)',strtolower($strFile))) {
					$arrFiles[] = $strFile;
				}
			}
		}

		natcasesort($arrFiles);

		foreach($arrFiles as $strFile) {
			$strFileExt  = substr(strrchr($strFile,'.'),1);
			$strFileName = str_replace('_',' ',substr($strFile,0,strrpos($strFile,'.')));
			$strResize   = 'images/img.php?src=';

			$intFilePos  = strpos($strFileName,' - ');
			if ($intFilePos > 0 && $intFilePos < 3) {
				$strFileName = substr($strFileName,$intFilePos + 3);
			}

			if (($blnManualThumbs && !strpos($strFile,'_th.')) || !$blnManualThumbs) {

				if ($strLink != '') $strList .= '<a href="' . $strLink . '">';

				$strList .= '<img ';

				if ($intFullW > 0 || $intFullH > 0) {
					$strList .= 'src="' . $strResize . $strAbs . $strFile .
								'&w=' . $intFullW . '&h=' . $intFullH . '&q=100&a=' . $strPos . '" ';

					$intFullW = ($intFullW == 0) ? '' : $intFullW;
					$intFullH = ($intFullH == 0) ? '' : $intFullH;
					$strList .= 'width="' . $intFullW . '" height="' . $intFullH . '" ';
				}
				else {
					$strList .= 'src="' . $strAbs . $strFile . '" ';

					list($intWidth, $intHeight) = getimagesize($strAbs . $strFile);
					$strList .= 'width="' . $intWidth . '" height="' . $intHeight . '" ';
				}

				$strList .= 'alt="' . $strFileName . '" ';

				if ($blnCaption) {
					$strList .= 'title="' . $strFileName . '" ';
				}

				if ($intThumbW > 0 || $intThumbH > 0) {
					if ($blnManualThumbs) {
						$strFile = str_replace('.' . $strFileExt,'_th.' . $strFileExt,$strFile);
					}
					$strList .= 'rel="' . $strResize . $strAbs . $strFile .
								'&w=' . $intThumbW . '&h=' . $intThumbH . '&q=90&a=' . $strPos . '"';
				}
				else {
					$strList .= 'rel="' . $strAbs . $strFile . '" ';
				}

				$strList .= ' />' . "\n";

				if ($strLink != '') $strList .= '</a>' . "\n";

			}
		}
	}

	return $strList;
}


/* Build Image Menu from Directory */
//  echo imageMenu('images/gallery/');
function imageMenu($strDir, $strMode='links', $strAnchor='') {
	$strQuery   = 'album';
	$strMenu    = '';
	$strSelDef  = false;
	$objDir     = opendir($strDir);
	$arrFiles   = array();
	$arrFolders = array();

	while (false !== ($strFile = readdir($objDir))) {
		if ($strFile != '.' && $strFile != '..' && is_dir($strDir . $strFile)) {
			$arrFolders[] = $strFile;
		}
		else {
			$strExt = substr(strrchr(strtolower($strFile),'.'),1);
			if (in_array($strExt,array('jpg','jpeg','gif','png'))) {
				$arrFiles[] = $strFile;
			}
		}
	}

	natcasesort($arrFolders);

	$strAlbum = isset($_GET[$strQuery]) ? $_GET[$strQuery] : '';
	if ($strAlbum == '' && count($arrFiles) == 0) { $strSelDef = true; }

	foreach ($arrFolders as $strFolder) {
		switch (strtolower($strMode)) {
			case 'select':
				if ($strMenu == '') {
					$strMenu .= '<select onchange="document.location.href=\'?' . $strQuery . '=\' + this.value + \'' . $strAnchor . '\';">' . "\n";
				}
				$strDiv   = "\n";
				$strSel   = ($strAlbum == $strFolder || $strSelDef) ? ' selected' : '';
				$strMenu .= '<option value="' . urlencode($strFolder) . '"' . $strSel . '>' . $strFolder . '</option>' . $strDiv;
				break;

			default:
				$strDiv   = ' &nbsp;|&nbsp; ';
				$strSel   = ($strAlbum == $strFolder || $strSelDef) ? 'font-weight:bold;' : 'font-weight:normal;';
				$strMenu .= '<a href="?' . $strQuery . '=' . urlencode($strFolder) . $strAnchor . '" style="' . $strSel . '">' . $strFolder . '</a>' . $strDiv;
				break;
		}
		$strSelDef = false;
	}

	$strMenu = substr($strMenu,0,strlen($strMenu)-strlen($strDiv));

	if (strtolower($strMode) == 'select') {
		$strMenu .= '</select>';
	}

	return $strMenu;
}


/* Print Site Map from Directory */
$strSiteMapPrefix = 'inc_|pnl_|db_|xml_|tpl_|_.|blog_|news_|events_|portfolio_|products_|account_';
$strSiteMapFiles  = '404|activate|notify';

function printSiteMap($strBaseDir, $bolSub) {
	global $strSiteMapPrefix;
	global $strSiteMapFiles;

	$strFileIndex   = 'index.php';
	$strFileSitemap = 'sitemap.php';
	$strFilePrev    = '';
	$blnFilePrev    = false;

	$objDir     = opendir($strBaseDir);
	$arrFiles   = array();
	$arrFolders = array();

	while (false !== ($strFile = readdir($objDir))) $arrFiles[] = $strFile;
	closedir($objDir);

	natcasesort($arrFiles);

	echo "\n" . '<ul>' . "\n";

	foreach($arrFiles as $strFileName) {
		$strFileClean  = str_replace('./','',$strFileName);
		$strFilePrefix = substr($strFileClean,0,strpos($strFileClean,'_')+1);
		$strFilePreExt = substr($strFileClean,0,strpos($strFileClean,'.'));

		if ($strFileClean != '.'
			&& $strFileClean != '..'
			&& $strFileClean != $strFileSitemap
			&& !preg_match('(' . $strSiteMapPrefix . ')',$strFilePrefix)
			&& !preg_match('(' . $strSiteMapFiles . ')',$strFilePreExt)
			&& $strFilePreExt != 'contact_location') {

			$strDir = $strBaseDir . '/' . $strFileClean;
			if (is_dir($strDir) && $bolSub) {
				printSiteMap($strDir,$bolSub);
			}
			else {
				if (preg_match('/[.\/].+\.(htm|html|php)$/',$strDir,$strFileClean)) {
					if ($strFilePrev == '') {
						echo "\t" . str_replace('<a ','<a style="font-weight:bold;" ',getTitle($strFileIndex));
						echo "\n\t" . '<ul>' . "\n";
					}

					$strFilePart = substr($strFileName,0,strpos($strFileName,'_')+1);
					if (!strpos($strFilePart,'_')) {
						$strFilePart = '';
					}

					if ($strFilePart == $strFilePrefix && substr($strFilePrefix, 0, -1) == substr($strFilePrev, 0, strlen($strFilePrefix) - 1) && !$blnFilePrev) {
						echo "\t\t" . '<ul>' . "\n";
						$blnFilePrev = true;
					}
					else if ($strFilePart != $strFilePrefix && $blnFilePrev) {
						echo "\t\t" . '</ul>' . "\n";
						$blnFilePrev = false;
					}

					if ($strFileName != $strFileIndex) {
						echo getTitle($strFileName);
					}

					$strFilePrev = $strFileName;
				}
			}
		}
	}

	echo getTitle($strFileSitemap);

	echo "\t" . '</ul>' . "\n";
	echo '</ul>' . "\n";
}

function getTitle($strFileName) {
	$strURL = str_ireplace('.php','',$strFileName) . '/';

	if (preg_match('(_)',$strURL)) {
		$strURL = str_replace('_','/',$strURL);
	}
	elseif ($strURL == 'index/') {
		$strURL = '';
	}

	$strOutput = "\t\t" . '<li><a href="' . $strURL . '">';

	$objFile = fopen($strFileName,'r');
	$strFile = fread($objFile,5000);

	fclose($objFile);

	$strDesc = preg_match('!<meta name="description" content="(.*?)"!i', $strFile, $arrMatch) ? $arrMatch[1] : '';
	$strDesc = preg_replace('!<\?php(.*?)\?>!i', '[...]', $strDesc);

	$strOutput = "\t\t" . '<li><a href="' . $strURL . '" title="' . $strDesc . '">';

	if (preg_match('/<title>.+<\/title>/i',$strFile,$strTitle)) {
		if (strpos($strTitle[0],'<?') > -1) {
			$strOutput .= ucwords(substr($strFileName,0,strrpos($strFileName,'.')));
		}
		else {
			$strOutput .= substr($strTitle[0],7,strpos($strTitle[0],'</title>')-7);
		}
	}
	else {
		$strOutput .= ucwords(substr($strFileName,0,strrpos($strFileName,'.')));
	}
	$strOutput .= '</a>';

	if (strpos($strFileName,'sitemap') > -1) {
		$strOutput .= '&nbsp;<a href="sitemap.xml" style="font-size:0.7em; vertical-align:middle;">(XML)</a>';
	}

	$strOutput .= '</li>' . "\n";

	return $strOutput;
}


/* RSS Feed Function */
function rss($strURL, $intHours) {
	if (substr($strURL,0,4) != 'http') {
		if (substr($strURL,0,1) != '/') {
			$strURL = '/' . $strURL;
		}
		$strSSL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		$strURL = $strSSL . $_SERVER['HTTP_HOST'] . $strURL;
	}

	$strRssXML = cache($strURL, '', $strDir = 'cache/rss/', $intHours, 'xml', '', true);

	return simplexml_load_string($strRssXML);
}


/* Multi-Sort Array Function */
//  multiSortArray($arrFull,array('pubDate'=>false,'title'=>true));
//  multiSortArray($arrFull,'title');  //Ascending
//  multiSortArray($arrFull,'random'); //Random
//  multiSortArray($arrFull,'');       //Unsorted
function multiSortArray($data, $field = '') {
	if ($field == 'random') {
		shuffle($data);
	}
	elseif (!is_array($field) && $field != '') {
		$field = array($field=>true);
	}
	if (is_array($field)) {
		$func = create_function('$a, $b use($field)', '
			$retval = 0;
			foreach ($field as $fieldname=>$asc) {
				if ($retval == 0) {
					$retval = strnatcmp($a[$fieldname], $b[$fieldname]);
					if(!$asc) $retval *= -1;
				}
			}
			return $retval;
		');
		usort($data, $func);
	}
	return $data;
}


/* Build Keywords Function */
// $strReturn: string = 'One, Two, Three' | array = array('One','Two','Three')
function buildKeywords($strInput, $intMax=25, $strReturn='string') {
	$strInput  = strip_tags($strInput);
	$strInput  = str_replace("\n",'',$strInput);
	$strInput  = str_replace("\r",'',$strInput);
	$strInput  = str_replace("\t",'',$strInput);
	$strInput  = str_replace('  ',' ',$strInput);
	$strInput  = strtolower($strInput);
	$strInput  = trim($strInput);

	$arrCommon = array(' the ',' of ',' and ',' a ',' to ',' in ',' is ',' you ',
		' that ',' it ',' he ',' was ',' for ',' on ',' are ',' as ',' with ',' his ',
		' they ',' I ',' at ',' be ',' this ',' have ',' from ',' or ',' one ',' had ',
		' by ',' word ',' but ',' not ',' what ',' all ',' were ',' we ',' when ',' your ',
		' can ',' said ',' there ',' use ',' an ',' each ',' which ',' she ',' do ',' how ',
		' their ',' if ',' will ',' up ',' other ',' about ',' out ',' many ',' then ',
		' them ',' these ',' so ',' some ',' her ',' would ',' make ',' like ',' him ',
		' into ',' time ',' has ',' look ',' two ',' more ',' write ',' go ',' see ',' number ',
		' no ',' way ',' could ',' people ',' my ',' than ',' first ',' water ',' been ',' call ',
		' who ',' oil ',' its ',' now ',' find ',' long ',' down ',' day ',' did ',' get ',
		' come ',' made ',' may ',' part ',' click ');
	$strInput   = str_replace($arrCommon,' ',$strInput);

	$arrLetters = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	$strTemp    = str_replace($arrLetters,'',strtolower($strInput));
	$strTemp    = str_replace(array('1','2','3','4','5','6','7','8','9','0',' '),'',$strTemp);
	$strInput   = str_replace(str_split($strTemp),'',$strInput);

	$arrTop     = array();
	$arrWords   = explode(' ',$strInput);
	if ($arrWords) {
		foreach ($arrWords as $strVal) {
			if(str_replace(' ','',$strVal) != '') {
				if (isset($arrTop[$strVal])) {
					$arrTop[$strVal]++;
				}
				else {
					$arrTop[$strVal] = 1;
				}
			}
		}
	}
	arsort($arrTop);

	$intCount  = 0;
	$strOutput = '';
	if ($arrTop) {
		foreach ($arrTop as $strKey => $strVal) {
			if ($intCount < $intMax) {
				if (strtolower($strReturn) == 'array') {
					$strOutput[] = $strKey;
				}
				else {
					$strOutput .= $strKey . ', ';
				}
			}
			$intCount++;
		}
	}

	if (strtolower($strReturn) != 'array') {
		$strOutput = substr($strOutput,0,-2);
	}

	return $strOutput;
}


/* Clean URL Function */
// cleanUrl('Clean URL Function');     //clean-url-function
// cleanUrl('Clean URL Function','_'); //clean_url_function
setlocale(LC_ALL, 'en_US.UTF8');
function cleanUrl($str, $delimiter='-', $replace=array()) {
	if( !empty($replace) ) {
		$str = str_replace((array)$replace, ' ', $str);
	}
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
	return $clean;
}


/* Build File Array */
// $arrFiles[0]['full'];
function listFiles($strPath='../images', $strExt='jpg, jpeg, gif, png', $intOutput=0, $blnRename=true, $strMask='*') {
	if (substr($strPath,-1) == '/') {
		$strPath = substr($strPath,0,strlen($strPath)-1);
	}

	if (file_exists($strPath)) {
		$strExt = str_replace(' ','',$strExt);
		$arrExt = explode(',',$strExt);
		$strExt = '';
		foreach($arrExt as $strItm) { //jpg,JPG
			$strExt .= strtolower($strItm) . ',' . strtoupper($strItm) . ',';
		}
		$strExt = substr($strExt,0,-1);

		$arrFiles = glob($strPath . '/' . $strMask . '.{' . $strExt . '}',GLOB_BRACE);
		natsort($arrFiles);

		$strIdDiv = ' - ';
		$blnExit  = false;
		$intCount = 0;
		$intFiles = count($arrFiles);

		if ($intFiles > 0) {
			foreach($arrFiles as $strFilePath) {
				$intCount++;
				$strFile = substr($strFilePath,(strrpos($strFilePath,'/')+1));

				if ($blnRename) {
					if (!strpos($strFile,$strIdDiv)) {
						$strFile = (string)$intCount . $strIdDiv . $strFile;
						@rename($strFilePath,$strPath . '/' . $strFile);
						$strFilePath = $strPath . '/' . $strFile;
					}

					$intFileId   = intVal(substr($strFile,0,strpos($strFile,$strIdDiv)));
					$strFileName = substr($strFile,strpos($strFile,$strIdDiv)+3);
					$strFileName = substr($strFileName,0,strrpos($strFileName,'.'));
				}
				else {
					$intFileId   = $intCount;
					$strFileName = substr($strFile,0,strrpos($strFile,'.'));
				}
				$strFileName = str_replace('_', ' ', $strFileName);

				$strFileExt  = substr(strrchr($strFilePath,'.'),1);
				$strFileSize = formatFileSize($strFilePath,2);
				$strFileDate = date('m/d/Y h:i:s A', filemtime($strFilePath));

				if (in_array(strtolower($strFileExt),array('jpg','jpeg','gif','png'))) {
					list($strFileWidth, $strFileHeight) = getimagesize($strFilePath);
				}
				else {
					$strFileWidth  = '';
					$strFileHeight = '';
				}

				$arrTemp = array(
					'name'    => $strFile,
					'id'      => $intFileId,
					'caption' => $strFileName,
					'ext'     => $strFileExt,
					'width'   => $strFileWidth,
					'height'  => $strFileHeight,
					'size'    => $strFileSize,
					'date'    => $strFileDate,
					'path'    => $strPath,
					'full'    => $strFilePath
				);

				switch ($intOutput) {
					case 1:  //First
						$arrOutput[0] = $arrTemp;
						$blnExit = true;
						break;

					case -1: //Last
						if ($intCount == $intFiles) {
							$arrOutput[0] = $arrTemp;
							$blnExit = true;
						}
						break;

					default: //All
						$arrOutput[$intFileId] = $arrTemp;
						break;
				}

				if ($blnExit) {
					break;
				}
			}

			$arrOutput = array_values($arrOutput);

			return $arrOutput;
		}
		else {
			return null; //Error: Files Not Found.
		}
	}
	else {
		return null; //Error: Path Not Found.
	}
}


/* Format File Size Function */
// formatFileSize('images/gallery/photo.jpg');
// formatFileSize(1234,1);
function formatFileSize($input, $intDecimals = 2) {
	if (is_numeric($input)) {
		$intBytes = $input;
	}
	else {
		$intBytes  = ($input && @is_file($input)) ? filesize($input) : NULL;
	}
	$strType   = 'B,K,M,G,T,P';
	$arrType   = explode(',', $strType);
	$intFactor = floor((strlen($intBytes) - 1) / 3);
	$strByte   = ($intFactor == 0) ? '' : 'B';
	return sprintf("%.{$intDecimals}f", $intBytes / pow(1024, $intFactor)) . ' ' . $arrType[$intFactor] . $strByte;
}


/* Authenticate Login */
function authLogin($strUsername, $strPassword, $strTable = 'login', $fldUsername = 'username', $fldPassword = 'password', $fldStatus = 'status') {
	@include('inc_db.php');

	$blnPass = is_null($strPassword) ? true : false;

	$sql = "
	SELECT *
	FROM  " . $strTable . "
	WHERE " . $fldStatus . " = 1
	AND   " . $fldUsername . " = '" . $strUsername . "' ";

	if (!$blnPass) $sql .= "AND " . $fldPassword . " = '" . $strPassword . "'";

	//debug($sql,1,1);
	$sqlRs = mysql_query($sql);
	$intRs = mysql_numrows($sqlRs);

	if ($intRs > 0) {
		while ($rs = mysql_fetch_array($sqlRs)) {
			return ($blnPass ? $rs[$fldPassword] : $rs['id']);
		}
	}
	else {
		return false;
	}

	@include('inc_db_close.php');
}


/* Encrypt/Decrypt Function */
function encryptDecrypt($strVal) {
	$strKey = 'Concept211';
	$strKey = str_replace(chr(32),'',$strKey);
	if (strlen($strKey) < 8) exit ('[Key Error]');
	$intKey = strlen($strKey) < 32 ? strlen($strKey) : 32;
	$arrKey = array();
	for ($i = 0; $i < $intKey; $i++) {
		$arrKey[$i] = ord($strKey[$i])&0x1F;
	}
	$j = 0;
	for ($i = 0; $i < strlen($strVal); $i++) {
		$e = ord($strVal[$i]);
		$strVal[$i] = $e&0xE0 ? chr($e^$arrKey[$j]) : chr($e);
		$j++;
		$j = $j == $intKey?0 : $j;
	}
	return $strVal;
}


/* Save Referrer */
function saveReferrer() {
	if (!isset($_COOKIE['referrer'])) {
		$strReferrer = isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : '';
		$strDomain   = preg_replace('/^(.*\.)?([^.]*\..*)$/', '$2', $_SERVER['HTTP_HOST']);
		$strDomain   = (strpos($strDomain, '.')) ? $strDomain : '.' . $strDomain;
		setcookie('referrer', $strReferrer, time() + 86400, '/', $strDomain);
	}
}


/* Get Referrer */
function getReferrer() {
	$strCookie = isset($_COOKIE['referrer']) ? stripslashes($_COOKIE['referrer']) : '';

	if ($strCookie != '') {
		$arrCookie   = parse_url($strCookie);
		$strReferrer = (count($arrCookie) > 1) ? preg_replace('/^(.*\.)?([^.]*\..*)$/', '$2', $arrCookie['host']) : '';
		$strDomain   = preg_replace('/^(.*\.)?([^.]*\..*)$/', '$2', $_SERVER['HTTP_HOST']);
		$strDirect   = isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : '';

		if ($strReferrer == $strDomain) {
			$strHTML = '<strong>Internal:</strong><br />';

			if ($strDirect != $strCookie) { //First Page Visited
				$strHTML .= '<a href="' . $strCookie . '">' . $strCookie . '</a>';
			}
			else {
				$strHTML .= '(Direct)';
			}
		}
		else {
			$strHTML = '<strong>External:</strong><br />';

			if (strlen($strCookie) > 60) {
				$strLabel = substr($strCookie, 0, 35) . ' ... ' . substr($strCookie, -25);
			}
			else {
				$strLabel = $strCookie;
			}

			$strHTML .= '<a href="' . $strCookie . '">' . $strLabel . '</a>';

			$arrList   = array();
			$arrList[] = array('Keywords'   => '(?:\?|&)(?:q|p|pq|text|query|search|utm_term)=(.*?)(?:&|$)');
			$arrList[] = array('Landing'    => '.*&url=(.*?)(?:&|$)');
			$arrList[] = array('Ad Landing' => '.*&adurl=(.*?)(?:&|$)');
			$arrList[] = array('Source'     => '.*&source=(.*?)(?:&|$)');
			$arrList[] = array('Position'   => '.*&cd=(.*?)(?:&|$)');

			foreach ($arrList as $arrItem) {
				foreach ($arrItem as $strTitle => $strPattern) {
					$strValue = preg_match('!' . $strPattern . '!i', $strCookie, $arrMatches) ? urldecode($arrMatches[1]) : '';

					if ($strValue != '') {
						$strHTML .= '<br /><br /><strong>' . $strTitle . ':</strong><br />';

						if ($strTitle == 'Keywords' && strpos($strCookie, 'google.')) {
							$strHTML .= '<a href="http://www.google.com/search?q=' . urlencode($strValue) . '">' . $strValue . '</a>';
						}
						else if ($strTitle == 'Keywords') {
							$strHTML .= $strValue;
						}
						else if (strpos($strValue, 'http') > -1) {
							$strHTML .= '<a href="' . $strValue . '">' . $strValue . '</a>';
						}
						else {
							$strHTML .= $strValue;
						}
					}
				}
			}
		}

		return '<small>' . $strHTML . '</small>';
	}
	else {
		return '';
	}
}


/* Get Record Value(s) */
// getDbVal('projects', 'id', "status = 1 ORDER BY sort");
// getDbVal('users', 'id', "firstname = 'Joe' AND status = 1 ORDER BY dateposted DESC", -1);
// $intType = getDbVal('types', 'id', "name = '" . $strType . "'", 1);
function getDbVal($strTable, $strField, $strQuery, $intLimit = 1) {
	$sql = "
	SELECT " . $strField . "
	FROM " . $strTable . "
	WHERE " . $strQuery;
	if ($intLimit > 0) $sql .= " LIMIT " . $intLimit;

	$sqlRs = mysql_query($sql);
	$intRs = mysql_numrows($sqlRs);

	if ($intRs > 0) {
		while ($rs = mysql_fetch_array($sqlRs)) {
			$arrRs[] = $rs[0];
		}

		return ($intLimit == 1) ? $arrRs[0] : $arrRs;
	}
	else {
		return NULL;
	}
}


/* Cache Function */
function cache($strData, $strKey = '', $strDir = 'cache/', $intHours = 24, $strExt = 'txt', $strPre = '', $blnEncode = false, $blnForceUpdate = false) {
	$strKey   = ($strKey == '') ? $strKey = $strData : $strKey;
	$strDir   = (substr($strDir, -1) != '/') ? $strDir . '/' : $strDir;
	$strPre   = ($strPre != '') ? $strPre . '-' : '';
	$intTime  = 3600 * $intHours;
	$strSubD  = 'SUBDOMAIN_DOCUMENT_ROOT';
	$strRoot  = $_SERVER[(isset($_SERVER[$strSubD]) ? $strSubD : 'DOCUMENT_ROOT')] . '/';
	$strPath  = $strRoot . $strDir;
	$strName  = $strPre . md5($strKey) . '.' . $strExt;
	$strFile  = $strPath . $strName;
	$intExp   = file_exists($strFile) ? time() - filemtime($strFile) : 0;
	$strRef   = isset($_GET['cache']) ? $_GET['cache'] : '';
	$strImgs  = 'jpg, jpeg, gif, png';
	$strClean = $strPath . $strPre . 'cache.' . $strExt;

	if (!file_exists($strPath) && !is_dir($strPath)) {
		$arrDir = explode('/', substr($strDir, 0, -1));
		$strMkDir = $strRoot;
		foreach ($arrDir as $strSub) {
			$strMkDir .= $strSub . '/';
			@mkdir($strMkDir, 0777);
		}
	}

	if (file_exists($strClean)
		&& (time() - filemtime($strClean)) > $intTime
		&& $strRef == ''
		&& $blnForceUpdate == false) {

		$objFiles = glob($strPath . $strPre . '*.' . $strExt);
		if ($objFiles) {
			$intDiff = time() - $intTime;
			foreach($objFiles as $strFileDel){
				if(file_exists($strFileDel) && filemtime($strFileDel) < $intDiff){
					unlink($strFileDel);
				}
			}
		}

		touch($strClean);
	}
	else {
		touch($strClean);
	}

	if (file_exists($strFile)
		&& filesize($strFile) > 0
		&& $intExp < $intTime
		&& $strRef == ''
		&& $blnForceUpdate == false) {

		if (in_array($strExt, explode(', ', $strImgs))) {
			$strOut = $strDir . $strName;
		}
		else {
			$strOut = file_get_contents($strFile);
		}
	}
	else if ($strData != '' && !is_null($strData)) {
		if (in_array($strExt, explode(', ', $strImgs))) {

			if (ini_get('allow_url_fopen')) {
				file_put_contents($strFile, file_get_contents($strData));
			}
			else {
				$objCurl = curl_init($strData);
				$objFile = fopen($strFile, 'wb');
				curl_setopt($objCurl, CURLOPT_FILE, $objFile);
				curl_setopt($objCurl, CURLOPT_HEADER, 0);
				curl_exec($objCurl);
				curl_close($objCurl);
				fclose($objFile);
			}

			$strOut = $strDir . $strName;

		}
		else {

			if (strtolower(substr($strData, 0, 4)) == 'http') {
				if (!$strOut = file_get_contents($strData)) {
					$arrError = error_get_last(); // $arrError['message']
					$strOut   = '';
				}
			}
			else {
				$strOut = $strData;
			}

			if ($objFile = @fopen($strFile, 'w')) {
				fwrite($objFile, $strOut, strlen($strOut));
				fclose($objFile);
			}

		}
	}
	else {
		$strOut = '';
	}

	if ($blnEncode) $strOut = utf8_encode($strOut);
	return (trim($strOut) == '') ? false : $strOut;
}


/* SEO Locations */
$strKeyPhrases = '';

//ex.    seoLocations('Key Phrase, Key Words');
function seoLocations($strKeys = '', $blnKeys = false, $strFormat = 'html', $strSource = 'inc_seo_locations.php', $intHours = 168, $strClass = 'seo_columns') {
	$strDir   = 'cache/seo/';
	$strCache = ($intHours > 0) ? cache('', $strKeys, $strDir, $intHours, $strFormat) : false;

	if ($strCache) {
		$strReturn = $strCache;
	}
	else {
		$arrKeys = explode(', ',$strKeys);
		$strText = file_get_contents(realpath($strSource));
		$arrText = explode("\n",$strText);
		natcasesort($arrText);

		$strList = '';
		foreach ($arrKeys as $strKey) {
			$strList .= ($strFormat == 'xml') ? '' : '<ul class="' . $strClass . '">';

			foreach ($arrText as $strLoc) {
				$strLoc = trim($strLoc);

				if ($strLoc != ''
				 && substr($strLoc, 0, 1) != '[') {

					$strURL = 'contact/' . (($blnKeys) ? cleanURL($strKey) . '/' : '') . cleanURL($strLoc) . '/';

					if ($strFormat == 'xml') {
						$strList .= '
						<url>
						<loc>' . 'http://' . $_SERVER['SERVER_NAME'] . '/' . $strURL . '</loc>
						<lastmod>' . date('Y-m-d') . '</lastmod>
						<changefreq>always</changefreq>
						<priority>0.8</priority>
						</url>
						';
					}
					else {
						$strList .= '<li><a href="' . $strURL . '">' . $strKey . (($strKeys != '') ? ' in ' : '') . $strLoc . '</a></li>';
					}

				 }
			}

			$strList .= ($strFormat == 'xml') ? '' : '</ul>';
		}

		if ($f = @fopen($strFile, 'w')) {
			fwrite($f, $strList, strlen($strList));
			fclose($f);
		}

		$strReturn = str_replace("\t", "", $strList);

		cache($strReturn, $strKeys, $strDir, $intHours, $strFormat);
	}

	return $strReturn;
}


/* Add Paragraph Function */
function addParagraph($strText) {
	return (substr(trim($strText), 0, 2) != '<p') ? '<p>' . $strText . '</p>' : $strText;
}


/* Write Log Function */
function writeLog($strData, $strFile = '', $blnInsertDate = false) {
	$strPath = dirname(__FILE__);
	$strDir  = strpos($strPath, '\\') ? '\\' : '/';

	if ($strFile == '') {
		$strFile = $_SERVER['SCRIPT_FILENAME'];
		if (!isset($strFile)) $strFile = '\history.log';
		$strFile = substr($strFile, strrpos($strFile, $strDir) + 1);
		$strFile = substr($strFile, 0, strrpos($strFile,'.')) . '.log';
	}

	if ($blnInsertDate) {
		$strDel  = strtolower(substr($strFile, -3)) == 'csv' ? ',' : "\t";
		$strData = date('Y-m-d H:i:s') . $strDel . $strData;
	}

	$strFile = strpos($strFile, __DIR__) >= 0 ? $strFile : $strPath . $strDir . $strFile;
	$objFile = fopen($strFile, 'a+');
	fwrite($objFile, $strData . PHP_EOL);
	fclose($objFile);
}


/* Tinify Functions */
function tinify($strFile, $strPath = 'images/*.*', $blnOutput = false, $blnOverride = false) {
	$strRoot  = __DIR__;
	$strDelim = DIRECTORY_SEPARATOR;
	$strLib   = $strRoot . '/libraries/tinify/';

	require_once($strLib . 'Tinify/Exception.php');
	require_once($strLib . 'Tinify/ResultMeta.php');
	require_once($strLib . 'Tinify/Result.php');
	require_once($strLib . 'Tinify/Source.php');
	require_once($strLib . 'Tinify/Client.php');
	require_once($strLib . 'Tinify.php');


	// Settings
	// gkUs-jxLLa9vAB9PFdX5-WXVVk8HRmzl (Gmail)
	// H7tMAhYRQMJQrP8sZFAgfKeuGzjkoexd (Centella)
	\Tinify\setKey('gkUs-jxLLa9vAB9PFdX5-WXVVk8HRmzl');

	$arrDir = array('icons', 'buttons'); // Excluded Directories
	$strLog = $strLib . 'images.log';


	// Current Month's Usage
	\Tinify\validate();
	$intMax   = 500;
	$intCount = \Tinify\compressionCount();


	// Parameters
	$strFile     = str_replace('/', $strDelim, $strFile);
	$strPath     = str_replace('/', $strDelim, $strPath);
	$blnOverride = ($blnOverride === 'true' || $blnOverride == 1) ? true : false;

	foreach($arrDir as $strKey => $strVal) {
		$arrDir[$strKey] = str_replace('/', $strDelim, $strVal);
	}

	// Read Log
	$strLogs = file_exists($strLog) ? file_get_contents($strLog) : '';
	$arrLogs = explode(PHP_EOL, $strLogs);
	if ($blnOverride) $arrLogs = array();

	// Process
	if ($blnOutput) echo '<ol style="font-family:monospace; color:#808080;">';

	if ($intCount < $intMax) {
		if ($strFile != '') {

			// Single File
			$strFile = strpos($strFile, $strRoot) >= 0 ? $strFile : $strRoot . $strDelim . $strFile;

			tinifyFile($strFile, $strLog, $arrLogs, $arrDir, $blnOutput, $blnOverride);

		}
		else {

			// Directories
			$blnSubDirs = strpos($strPath, '*.*') ? false : true;
			$strPath    = str_replace('*.*', '', $strPath);
			$strPath    = $strRoot . $strDelim . $strPath;

			if (file_exists($strPath) && is_dir($strPath)) {

				$strPath  = str_replace('/', $strDelim, $strPath);

				if ($blnSubDirs) {
					$arrFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($strPath));

					foreach ($arrFiles as $strFilePath => $strFile) {
						tinifyFile($strFilePath, $strLog, $arrLogs, $arrDir, $blnOutput, $blnOverride);
					}
				}
				else {
					if ($objFs = opendir($strPath)) {
						while (($strFile = readdir($objFs)) != false) {
							if ($strFile != '.' && $strFile != '..') {
								$strFileExt = substr(strrchr($strFile,'.'),1);
								if ($strFileExt != '') {
									tinifyFile($strPath . $strFile, $strLog, $arrLogs, $arrDir, $blnOutput, $blnOverride);
								}
							}
						}
						closedir($objFs);
					}
				}

			}
			else {
				error('Invalid Directory');
			}

		}
	}

	if ($blnOutput) echo '</ol>';
	if ($blnOutput && $intCount != '') debug('<b>' . $intCount . '</b>/' . $intMax);

}

function tinifyFile($strFilePath, $strLog, $arrLogs, $arrDir, $blnOutput = false, $blnOverride = false) {
	$strRoot     = __DIR__;
	$strDelim    = DIRECTORY_SEPARATOR;
	$arrExt      = array('jpg', 'jpeg', 'png', 'apng', 'tmp');
	$strFileDir  = pathinfo($strFilePath, PATHINFO_DIRNAME);
	$strFileName = pathinfo($strFilePath, PATHINFO_BASENAME);
	$strFileExt  = pathinfo($strFilePath, PATHINFO_EXTENSION);
	$strLastDir  = substr($strFileDir, strrpos($strFileDir, $strDelim) + 1);

	if ((in_array($strFileExt, $arrExt) || substr($strFileName, 0, 3) == 'tim')
	 && !in_array($strFilePath, $arrLogs)
	 && !in_array($strLastDir, $arrDir)) {

		// Compress File
		$objSrc = \Tinify\fromFile($strFilePath);
		$objSrc->toFile($strFilePath);

		// Save to Log
		if (!$blnOverride
		 && $strFileExt != 'tmp'
		 && !(substr($strFileName, 0, 3) == 'tim' && $strFileExt == '')) {
			writeLog($strFilePath, $strLog, false);
		}

		// Write to Page
		if ($blnOutput) {
			echo '<li>' . str_replace('/', $strDelim, $strFileDir) .
				 $strDelim . '<b style="color:green;">' . $strFileName . '</b></li>';

			// Output Immediately
			echo str_repeat(' ', 1024*64);
			flush();
		}

	}

}