<?php
/**
 * Yii Requirement Checker script
 *
 * This script will check if your system meets the requirements for running
 * Yii-powered Web applications.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 * @package system
 * @since 1.0
 */

/**
 * @var array List of requirements (name, required or not, result, used by, memo)
 */
$requirements = array(
	array(
		t('yii','PHP version'),
		true,
		version_compare(PHP_VERSION,"5.1.0",">="),
		'<a href="http://www.yiiframework.com">Yii Framework</a>',
		t('yii','PHP 5.1.0 or higher is required.')),
	array(
		t('yii','$_SERVER variable'),
		true,
		($message=checkServerVar())==='',
		'<a href="http://www.yiiframework.com">Yii Framework</a>',
		$message),
	array(
		t('yii','Reflection extension'),
		true,
		class_exists('Reflection',false),
		'<a href="http://www.yiiframework.com">Yii Framework</a>',
		''),
    array(
        t('yii','PCRE extension'),
        true,
        extension_loaded("pcre"),
		'<a href="http://www.yiiframework.com">Yii Framework</a>',
    	''),
    array(
		t('yii','SPL extension'),
		true,
		extension_loaded("SPL"),
		'<a href="http://www.yiiframework.com">Yii Framework</a>',
		''),
	array(
		t('yii','DOM extension'),
		false,
		class_exists("DOMDocument",false),
		'<a href="http://www.yiiframework.com/doc/api/CWsdlGenerator">CWsdlGenerator</a>',
		''),
    array(
    	t('yii','PDO extension'),
        false,
    	extension_loaded('pdo'),
		t('yii','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
        ''),
    array(
    	t('yii','PDO SQLite extension'),
        false,
        extension_loaded('pdo_sqlite'),
		t('yii','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yii','This is required if you are using SQLite database.')),
    array(
    	t('yii','PDO MySQL extension'),
        false,
        extension_loaded('pdo_mysql'),
		t('yii','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yii','This is required if you are using MySQL database.')),
    array(
    	t('yii','PDO PostgreSQL extension'),
        false,
        extension_loaded('pdo_pgsql'),
		t('yii','All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>'),
		t('yii','This is required if you are using PostgreSQL database.')),
	array(
    	t('yii','Memcache extension'),
		false,
		extension_loaded("memcache"),
		'<a href="http://www.yiiframework.com/doc/api/CMemCache">CMemCache</a>',
		''),
	array(
		t('yii','APC extension'),
		false,
		extension_loaded("apc"),
		'<a href="http://www.yiiframework.com/doc/api/CApcCache">CApcCache</a>',
		''),
	array(
		t('yii','Mcrypt extension'),
		false,
		extension_loaded("mcrypt"),
		'<a href="http://www.yiiframework.com/doc/api/CSecurityManager">CSecurityManager</a>',
		'This is required by encrypt and decrypt methods.'),
	array(
		t('yii','SOAP extension'),
		false,
		extension_loaded("soap"),
		'<a href="http://www.yiiframework.com/doc/api/CWebService">CWebService</a>, <a href="http://www.yiiframework.com/doc/api/CWebServiceAction">CWebServiceAction</a>',
		''),
	array(
		t('yii','GD extension'),
		false,
		extension_loaded('gd'),
		'<a href="http://www.yiiframework.com/doc/api/CCaptchaAction">CCaptchaAction</a>',
		''),
);

function checkServerVar()
{
	$vars=array('HTTP_HOST', 'SERVER_NAME', 'SERVER_PORT', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'PHP_SELF', 'HTTP_ACCEPT', 'HTTP_USER_AGENT');
	$missing=array();
	foreach($vars as $var)
	{
		if(!isset($_SERVER[$var]))
			$missing[]=$var;
	}
	if(!empty($missing))
		return t('yii','$_SERVER does not have {vars}.',array('{vars}'=>implode(', ',$missing)));

	if(realpath($_SERVER["SCRIPT_FILENAME"])!==realpath(__FILE__))
		return t('yii','$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

	if(!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
		return t('yii','Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

	if(!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"],$_SERVER["SCRIPT_NAME"])!==0)
		return t('yii','Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

	return '';
}

function getYiiVersion()
{
	$coreFile=dirname(__FILE__).'/../framework/YiiBase.php';
	if(is_file($coreFile))
	{
		$contents=file_get_contents($coreFile);
		$matches=array();
		if(preg_match('/public static function getVersion.*?return \'(.*?)\'/ms',$contents,$matches)>0)
			return $matches[1];
	}
	return '';
}

/**
 * Returns a localized message according to user preferred language.
 * @param string message category
 * @param string message to be translated
 * @param array parameters to be applied to the translated message
 * @return string translated message
 */
function t($category,$message,$params=array())
{
	static $messages;

	if($messages===null)
	{
		$messages=array();
		if(($lang=getPreferredLanguage())!==false)
		{
			$file=dirname(__FILE__)."/messages/$lang/yii.php";
			if(is_file($file))
				$messages=include($file);
		}
	}

	if(empty($message))
		return $message;

	if(isset($messages[$message]) && $messages[$message]!=='')
		$message=$messages[$message];

	return $params!==array() ? strtr($message,$params) : $message;
}

function getPreferredLanguage()
{
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($n=preg_match_all('/([\w\-]+)\s*(;\s*q\s*=\s*(\d*\.\d*))?/',$_SERVER['HTTP_ACCEPT_LANGUAGE'],$matches))>0)
	{
		$languages=array();
		for($i=0;$i<$n;++$i)
			$languages[$matches[1][$i]]=empty($matches[3][$i]) ? 1.0 : floatval($matches[3][$i]);
		arsort($languages);
		foreach($languages as $language=>$pref)
			return strtolower(str_replace('-','_',$language));
	}
	return false;
}

function getServerInfo()
{
	$info[]=isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
	$info[]='<a href="http://www.yiiframework.com/">Yii Framework</a>/'.getYiiVersion();
	$info[]=@strftime('%Y-%m-%d %H:%m',time());
	return implode(' ',$info);
}

function renderFile($_file_,$_params_=array())
{
	extract($_params_);
	require($_file_);
}

$result=1;  // 1: all pass, 0: fail, -1: pass with warnings
$n=count($requirements);
for($i=0;$i<$n;++$i)
{
	$requirement=$requirements[$i];
	if($requirement[1] && !$requirement[2])
		$result=0;
	else if($result>0 && !$requirement[1] && !$requirement[2])
		$result=-1;
	$requirements[$i][0]=$requirements[$i][0];
	$requirements[$i][3]=$requirements[$i][3];
	$requirements[$i][4]=$requirements[$i][4];
	if($requirements[$i][4]==='')
		$requirements[$i][4]='&nbsp;';
}

$lang=getPreferredLanguage();
$viewFile=dirname(__FILE__)."/views/$lang/index.php";
if(!is_file($viewFile))
	$viewFile=dirname(__FILE__).'/views/index.php';

renderFile($viewFile,array(
	'requirements'=>$requirements,
	'result'=>$result,
	'serverInfo'=>getServerInfo()));

