<?php
/*
 * Module	: KudzuPHP.php
 * Author	: Andrew Friedl @ TriLogic Industries, LLC
 * Descr	: PHP Version of the KudzuASP Template Engine.
 *          : Modified tag structure for use in Wordpress.
 * Version  : 1.6.0
 * Link     : http://www.trilogicllc.com/
 * License  : GPLv2, Commercial License Available
 */
define('KUDZUPHP',true);
define('KUDZUPHP_VERSION', '1.6.0');
$KudzuLIB = new CKudzuLib();

class CKudzuLibItem {
	var $tags = array();
	var $libName = '';
	var $libDesc = '';
	var $libFile = '';
	var $libFunc = '';
	var $libVers = '0.0';
	
	function setTag($tag,$obj) {
		$this->tags[strtolower($tag)] = $obj;
	}
	function setTagFn($tag,$fn,$ob=null) {
		$obj = new CTHCall();
		$obj->setFunction($fn);
		$obj->setObject($ob);
		$this->setTag($tag,$obj);
	}
	function getTag($tag) {
		return $this->tags[strtolower($tag)];
	}
	function libInit($libPath,$libName) {
		$this->libName = strtoupper($libName);
		$this->libFile = $libPath.'KudzuLib_'.$this->libName.'.php';
		$this->libFunc = 'KudzuLibImport_'.$this->libName;
	}
	function libImport() {
		$func = $this->libFunc;
		require_once($this->libFile);
		$func($this);
	}
	function setTags($eng) {
		$keys = array_keys($this->tags);
		foreach($keys as $key)
			$eng->setHandler($key,$this->tags[$key]);
	}
}

class CKudzuLib {
	var $tagLibs = array();
	var $libPath = array();
	var $libCurr = null;

    function __construct() {
		array_push($this->libPath,'.\\');
	}
	function libExists($libName) {
		return array_key_exists(strtolower($libName),$this->tagLibs);
	}
	function libFind($libName) {
		$lib = strtoupper($libName);
		for($idx=count($this->libPath)-1;$idx>=0;$idx--) {
			$pth = $this->libPath[$idx].'KudzuLib_'.$lib.'.php';
			if (file_exists($pth))
				return $this->libPath[$idx];
		}
		return null;
	}
	function libImport($libName) {
		if ($this->libExists($libName))
			return $this->libGet($libName);
		$path = $this->libFind($libName);
		if (is_null($path)) return null;
		$this->libCurr = new CKudzuLibItem();
		$this->libCurr->libInit($path,$libName);
		$this->libCurr->libImport();
		$this->tagLibs[strtolower($libName)] = $this->libCurr;
		return $this->libCurr;
	}
	function libGet($libName) {
		return $this->tagLibs[strtolower($libName)];
	}
	function libSetTags($libName,$eng) {
		$this->libImport($libName);
		$libObj = $this->libGet($libName);
		if ($libObj == null) return false;
		$libObj->setTags($eng);
	}
	function libPathPush($libPath) {
		array_push($this->libPath,$libPath);
	}
	function libPathPop() {
		array_push($this->libPath);
	}
	function setLibPath($libPath) {
		$this->libPath[0] = $libPath;
	}
}

class CKudzuWriter {
	function write($text) { echo $text;	}
}

class CKudzuBufferWriter
{
	var $mContent = '';
	function write($text) {
		$this->mContent .= $text;
	}
	function getContent() {
		return $this->mContent;
	}
	function setContent($text) {
		$this->mContent = $text;
	}
}

class CMatchBuilder {
	var $matches = array();
	var $result  = array();
	var $thisOffset=0;
	var $thisLength=0;
	var $lastOffset=0;
	var $lastLength=0;
	var $nextOffset=0;
	var $input='';
	var $length=0;
	
	function getCount() {
		return count($this->result);
	}
	function getInput() {
		return $this->input;
	}
	function buildMatches($regex, $input) {
		$this->lastOffset=0;
		$this->lastLength=0;
		$this->input = $input;
		$this->length = strlen($input);
		
		preg_match_all($regex, $input, $this->matches);
		$thisCount = count($this->matches[0]);
		
		for ($idx=0; $idx < $thisCount; $idx++) {
			$this->nextOffset = $this->lastOffset + $this->lastLength;
			$this->thisOffset = strpos($input, $this->matches[0][$idx], $this->nextOffset);
			$this->thisLength = strlen($this->matches[0][$idx]);
			if ($this->thisOffset > $this->nextOffset) {
				$this->addNonMatch();
			}
			$this->addMatch();
			$this->lastOffset = $this->thisOffset;
			$this->lastLength = $this->thisLength;
		}
		$this->nextOffset = $this->lastOffset + $this->lastLength;
		if ($this->nextOffset < $this->length) {
			$this->thisOffset = $this->length;
			$this->addNonMatch();
		}
		return count($this->result);
	}
	function addNonMatch() {
		$tempResult = array();
		$tempLength = $this->thisOffset - $this->nextOffset;
		$tempResult['offset'] = $this->nextOffset;
		$tempResult['length'] = $tempLength;
		$tempResult['match']  = false;
		array_push($this->result,$tempResult);
	}
	function addMatch() {
		$tempResult = array();
		$tempResult['offset'] = $this->thisOffset;
		$tempResult['length'] = $this->thisLength;
		$tempResult['match']  = true;
		array_push($this->result,$tempResult);
	}
	function hasMatches() {
		return (count($this->result) > 0);
	}
	function getMatches() {
		return $this->result;
	}
	function getMatch($index) {
		return $this->result[$index];
	}
	function getMatchText($match) {
		return substr($this->input, $match['offset'], $match['length']);
	}	
}

class CTHCall {
	var $mOb = null;
	var $mFn = null;
	function handleTag($node) {
		if (is_null($this->mOb)) {
			$funct = $this->mFn;
			$funct($node);
			return;
		} 
		call_user_func(array($this->mOb,$this->mFn),$node);
	}
	function setObject($ob) {
		$this->mOb = $ob;
	}
	function getObject() {
		return $this->mOb;
	}
	function setFunction($fn) {
		$this->mFn = $fn;
	}
	function getFunction() {
		return $this->mFn;
	}
}

class CKudzuNode {
	var $iid = '';
	var $mStartTag = '';
	var $mStopTag = '';
	var $mContent = '';
	var $mEvalProc = '';
	var $mNodes = array();
	var $mParams = array();
	var $mEngine = null;
	
	function getID() {
		return $this->iid;
	}
	function setID($id) {
		$this->iid = $id;
	}
	function getEngine() {
		return $this->mEngine;
	}
	function setEngine($engine) {
		$this->mEngine = $engine;
		for($idx=0; $idx<count($this->mNodes); $idx++) {
			$this->mNodes[$idx]->setEngine($engine);
		}
	}
	function getStartTag() {
		return $this->mStartTag;
	}
	function setStartTag($value='') {
		$this->mStartTag = $value;
	}
	function getStopTag() {
		return $this->mStopTag;
	}
	function setStopTag($value='') {
		$this->mStopTag = $value;
	}
	function getContent() {
		return $this->mContent;
	}
	function setContent($value) {
		$this->mContent = $value;
	}
	function getEvalProc() {
		return $this->mEvalProc;
	}
	function setEvalProc($value) {
		$this->mEvalProc = $value;
	}
	function getNodeCount() {
		return count($this->mNodes);
	}
	function getNodeItem($index) {
		return $this->mNodes[$index];
	}
	function addNode($node) {
		array_push($this->mNodes,$node);
	}
	function locateNode($sid) {
		for ($idx=0;$idx<count($this->mNodes);$idx++) {
			if (strcasecmp($sid,$this->mNodes[$idx]->iid) === 0)
				return $this->mNodes[$idx];
		} return null;
	}
	function getParamCount() {
		return count($this->mParams);
	}
	function getParamItem($index,$default=null) {
		if ($index >= count($this->mParams))
			return $default;
		$result = $this->mParams[$index];
		if (strpos($result,'((')>=0)
			$result = $this->mEngine->replaceParams($result,$this->mEngine);
		return $result;
	}
	function addParam($param) {
		array_push($this->mParams,$param);
	}
	function assertParamCount($minCount,$errMsg) {
		if (count($this->mParams)<$minCount) {
			return $this->appendTagError($errMsg);
		} return true;
	}
	function evalProcString() {
		$handler = $this->mEngine->getHandler($this->mEvalProc);
		if (is_null($handler))
			throw new Exception('Invalid Handler:'.$this->mEvalProc);
		$handler = $this->mEngine->getHandler($this->iid);
		$handler->handleTag($this);
	}
	function evalParamString($param) {
		if (! $this->mEngine->hasValue($param))
			throw new Exception('Invalid Parameter:'.$param);
		return $this->mEngine->getValue($param);
	}
	function evalNode() {
		if ((strlen($this->iid) == 0) || (strlen($this->mEvalProc) == 0)) {
			$this->mEngine->contentAppend($this->mStartTag);
			$this->mEngine->contentAppend($this->mContent);
			$this->evalNodes();
			$this->mEngine->contentAppend($this->mStopTag);
		} else {
			try { 
				$this->evalProcString();
			} catch (Exception $e) {
				$this->mEngine->contentAppend('Exception: ' . $e->getMessage());
			}
		}
	}
	function evalNodes() {
		for($idx=0; $idx<count($this->mNodes); $idx++) {
			$this->mNodes[$idx]->evalNode();
		}
	}
	function evalChildNode($name) {
		$node = $this->locateNode($name);
		if ($node == null) return false;
		$node->evalNodes();
		return true;
	}
	function stackPush() {
		$this->mEngine->contentPush();
	}
	function stackPop() {
		$this->mEngine->contentAppend($this->mEngine->contentPop());
	}
	function appendText($text) {
		$node = new CKudzuNode();
		$node->setContent($text);
		$node->setID('_txt_');
		array_push($this->mNodes,$node);
	}
	function appendError($error) {
		$this->appendContent('<i><b>Error:</b>' . $this->mEvalProc);
		if (strlen($error) > 0)
			$this->appendContent('|' . $error);
		$this->appendContent('</i>');
	}
	function appendContent($text) {
		$this->mEngine->contentAppend($text);
	}
	function appendTagError($text) {
		$this->stackPush(true);
		$this->appendError($text);
		$this->stackPop(true);
		return false;
	}
	function isTrue($var) {
		return $this->mEngine->isTrue($var);
	}
}

class CKudzuCompiler {
	var $RGX_TAG = '/<!--\\{\\\\?[^\\}]+\\\\?\\}-->/';
	var $RGX_FRM = '/(\\<!--\\{\\/?)|(\\/?\\}--\\>)/';
	var	$mDebug = false;
	var $mParseStack = array();
	var $mParseLevel = 0;
	var $mFile = 0;
	var $mWriter = null;
	
	function CKudzuCompiler() {
		$this->mWriter = new CKudzuWriter();
	}
	function setDebug($value) {
		$this->mDebug = $value;
	}
	function getDebug() {
		return $this->mDebug;
	}
	function setWriter($writer) {
		$this->mWriter = $writer;
	}
	function getWriter($writer) {
		return $this->mWriter;
	}
	function initParseStack() {
		$this->mParseStack = array();
		array_push($this->mParseStack, new CKudzuNode());
		$this->mParseLevel = 0;
		$this->mParseStack[0]->setID('_root');
	}
	function parsePush($node) {
		$this->mParseLevel+=1;
		array_push($this->mParseStack,$node);
	}
	function parsePop() {
		$result = array_pop($this->mParseStack);
		$this->mParseLevel-=1;
		return $result;
	}
	function parsePeek() {
		return $this->mParseStack[$this->mParseLevel];
	}
	function getParseLevel() {
		return $this->mParseLevel;
	}
	function parseFile($sFileName) {
		$template = file_get_contents($sFileName);
		return $this->parseTemplate($template);
	}
	function parseTemplate($template) {
		$this->initParseStack();
		$mb = new CMatchBuilder();
		$mb->buildMatches($this->RGX_TAG, $template);

		for ($idx=0; $idx<$mb->getCount(); $idx++) {
			$m = $mb->getMatch($idx);
			if ($m['match']) {
				$this->handleTagMatch($mb->getMatchText($m));
			} else {
				$this->handleNodeText($mb->getMatchText($m));
			}
		}
		while ($this->mParseLevel > 0) {
			$node = $this->parsePop();
			$this->parsePeek()->addNode($node);
		}
		return $this->mParseStack[0];
	}
	function isFormalEndTag($sTag) {
		return (strpos($sTag, '<!--{/') === 0);
	}
	function isTermedTag($sTag) {
		$isTermed = (strrpos($sTag, '/}-->') === (strlen($sTag)-5));
		return (strrpos($sTag, '/}-->') === (strlen($sTag)-5));
	}
	function handleTagMatch($match) {
		if ($this->isFormalEndTag($match)) {
			$this->parseEndTag($match);
		} elseif ($this->isTermedTag($match)) {
			$this->parseTermedTag($match);
		} else {
			$this->parseBeginTag($match);
		}
	}
	function handleNodeText($text) {
		$this->parsePeek()->appendText($text);
	}
	function parseTagProperties($match, $node, $setID) {
		if ($this->mDebug) {
			$node->setStartTag($match);
		} else {
			$node->setStartTag();
		}
		$temp = preg_replace($this->RGX_FRM,'',$match);
		$dir = explode('|',$temp);
		if ($setID) {
			$node->setID($dir[0]);
			$node->setEvalProc($dir[0]);
			if (count($dir) > 1) {
				for ($idx=1; $idx<count($dir); $idx++) {
					$node->addParam($dir[$idx]);
				}
			}
		}
	}
	function parseBeginTag($match) {
		$node = new CKudzuNode();
		$this->parsePush($node);
		$this->parseTagProperties($match,$node,true);
		if ($this->mDebug)
			$this->dumpParseInfo($node->getStartTag());
	}
	function parseEndTag($match) {
		$temp = preg_replace($this->RGX_FRM,'',$match);
		$dir = explode('|',$temp);		
		$id = $dir[0];
		$term = strcasecmp($id,$this->parsePeek()->getID());
		while(($this->mParseLevel > 0) && ($term!==0)) {
			$node = $this->parsePop();
			$this->parsePeek()->addNode($node);
			$term = strcasecmp($id,$this->parsePeek()->getID());
		}
		$term = strcasecmp($id,$this->parsePeek()->getID());
		if (strcasecmp($this->parsePeek()->getID(),$id) == 0) {
			$node = $this->parsePop();
			$node->setStopTag($match);
			$this->parsePeek()->addNode($node);
		} else {
		}
		if ($this->mDebug)
			$this->dumpParseInfo2($node->getStopTag());
	}
	function parseTermedTag($match) {
		$node = new CKudzuNode();
		$this->parseTagProperties($match, $node, true);
		$this->parsePeek()->addNode($node);
		if ($this->mDebug)
			$this->dumpParseInfo2($node->getStartTag());
	}
	function dumpParseInfo2($match) {
		$temp = '00'.($this->mParseLevel+1);
		$temp = substr($temp, strlen($temp)-2, 2);
		$temp .= ':';
		$temp .= str_repeat('&nbsp;&nbsp;|', $this->mParseLevel+1);
		$temp .= htmlentities($this->strTag48($match));
		$this->mWriter->write($temp.'<br/>');
	}	
	function dumpParseInfo($match) {
		$temp = '00'.$this->mParseLevel;
		$temp = substr($temp, strlen($temp)-2, 2);
		$temp .= ':';
		$temp .= str_repeat('&nbsp;&nbsp;|', $this->mParseLevel);
		$temp .= htmlentities($this->strTag48($match));
		$this->mWriter->write($temp.'<br/>');
	}
	function strTag48($tag) {
		$len = strlen($tag);
		if ($len<=48) return $tag;
		if ($this->isTermedTag($tag))
			return substr($tag,0,38).' ... /}-->';
		return substr($tag,0,39).' ... }-->';
	}
}

class CKudzuEngine {
	var $mRunStack=array();
	var $mRunLevel=0;
	var $mDebug=false;
	var $mHandlers=array();
	var $mIterators=array();
	var $mValues=array();
	var $mNodeTree=null;
	var $RGX_FLD='/\\{\\{[^}]+\\}\\}/';
	var $RGX_SUB='/\\(\\([^)]+\\)\\)/';
	var $isSetup=false;
	var $mWriter=null;
	
	function CKudzuEngine() {
		$this->reset();
		$this->mWriter = new CKudzuWriter();
	}
	function reset() {
		$this->resetValues();
		$this->resetRuntime();
		$this->resetHandlers();
		$this->isSetup = true;
	}
	function resetValues() {
		$this->mValues = array();
		$this->mIterators = array();
		$this->putValue('KudzuPHP_Version',KUDZUPHP_VERSION);
	}
	function resetRuntime() {
		$this->mRunStack = array();
		array_push($this->mRunStack, '');
		$this->mRunLevel = 0;
	}	
	function resetHandlers() {
		$this->mHandlers = array();
		$this->setHandlerFn('Case','tag_CASE',$this);
		$this->setHandlerFn('Cycle','tag_CYCLE',$this);
		$this->setHandlerFn('ForArray','tag_FORARRAY',$this);
		$this->setHandlerFn('ForEach','tag_FOREACH',$this);
		$this->setHandlerFn('If','tag_IFTHEN',$this);
		$this->setHandlerFn('IfTrue','tag_IFTRUE',$this);
		$this->setHandlerFn('IfFalse','tag_IFFALSE',$this);
		$this->setHandlerFn('Ignore','tag_IGNORE',$this);
		$this->setHandlerFn('Import','tag_IMPORT',$this);
		$this->setHandlerFn('Iterate','tag_ITERATE',$this);
		$this->setHandlerFn('Random','tag_RANDOM',$this);
		$this->setHandlerFn('Replace','tag_REPLACE',$this);
		$this->setHandlerFn('Rep','tag_REPLACE',$this);
		$this->setHandlerFn('Subst','tag_SUBST',$this);
		$this->setHandlerFn('Sub','tag_SUBST',$this);
		$this->setHandlerFn('SetValue','tag_SETVALUE',$this);
	}
	function setDebug($value) {
		$this->mDebug = $value;
		if ($this->mDebug)
			$this->mWriter = new CKudzuBufferWriter();
	}
	function getDebug() {
		return $this->mDebug;
	}
	function getWriter() {
		return $this->mWriter;
	}
	function setWriter($writer) {
		$this->mWriter = $writer;
	}
	function setHandler($name, $objHandler) {
		$key = strtolower($name);
		$prev = $this->mHandlers[$key];
		$this->mHandlers[$key] = $objHandler;
		return $prev;
	}
	function setHandlerFn($name,$fnName,$obInst=NULL) {
		$obj = new CTHCall();
		$obj->setObject($obInst);
		$obj->setFunction($fnName);
		$this->setHandler($name,$obj);
	}
	function getHandler($name) {
		return $this->mHandlers[strtolower($name)];
	}
	function hasHandler($name) {
		return array_key_exists(strtolower($name),$this->mHandlers);
	}
	function setValue($name, $value) {
		return $this->putValue($name, $value);
	}
	function putValue($name, $value) {
		$key = strtolower($name);
		$prev = $this->mValues[$key];
		$this->mValues[$key] = $value;
		return $prev;
	}
	function getValue($name) {
		return $this->mValues[strtolower($name)];
	}
	function hasValue($name) {
		return array_key_exists(strtolower($name),$this->mValues);
	}
	function parseFile($file) {
		$this->resetRuntime();
		$T_COMPILER = new CKudzuCompiler();
		$T_COMPILER->setWriter($this->getWriter());
		$T_COMPILER->setDebug($this->mDebug);
		$node = $T_COMPILER->parseFile($file);
		$this->mNodeTree = $node;
		$node->setEngine($this);
	}
	function parseString($template) {
		$this->resetRuntime();
		$T_COMPILER = new CKudzuCompiler();
		$T_COMPILER->setWriter($this->getWriter());
		$T_COMPILER->setDebug($this->mDebug);
		$node = $T_COMPILER->parseTemplate($template);
		$this->mNodeTree = $node;
		$node->setEngine($this);
	}
	function evalTemplate() {
		try {
			$this->mNodeTree->evalNode();
		} catch (Exception $e) {
			$this->contentAppend('EXCEPTION:'.$e->getMessage());
		}
		$this->contentFlush();
	}
	function executeFile($file) {
		if ($this->mDebug) {
			$this->setWriter(new CKudzuBufferWriter());
			$this->mWriter->write('<code>');
			$this->parseFile($file);
			$this->mWriter->write('</code>');
			$this->setContent($this->getWriter()->getContent());
		} else {
			$this->parseFile($file);
			$this->evalTemplate();
		}
	}
	function executeString($template) {
		if ($this->mDebug) {
			$this->setWriter(new CKudzuBufferWriter());
			$this->mWriter->write('<code>');
			$this->parseString($template);
			$this->mWriter->write('</code>');
			$this->setContent($this->getWriter()->getContent());
		} else {
			$this->parseString($template);
			$this->evalTemplate();
		}
	}
	function getContentLevel() {
		return $this->mRunLevel;
	}
	function getContent() {
		return $this->mRunStack[$this->mRunLevel];
	}
	function setContent($content) {
		$this->mRunStack[$this->mRunLevel] = $content;
	}
	function contentPush() {
		array_push($this->mRunStack,'');
		$this->mRunLevel++;
	}
	function contentPop() {
		$content = $this->mRunStack[$this->mRunLevel];
		if ($this->mRunLevel > 0) {
			$this->mRunLevel--;
			array_pop($this->mRunStack);
		}
		return $content;
	}
	function contentFlush() {
		for ($idx = 0; $idx <= $this->mRunLevel; $idx++) {
			$this->mWriter->write($this->mRunStack[$idx]);
			$this->mRunStack[$idx]='';
		}
	}
	function contentAppend($content) {
		$this->setContent($this->getContent().$content);
	}
	function replaceFields($text) {
		$result = '';
		$mb = new CMatchBuilder();
		$mb->buildMatches($this->RGX_FLD, $text);
		for($idx=0; $idx<$mb->getCount(); $idx++) {
			$m = $mb->getMatch($idx);
			$key = $mb->getMatchText($m);
			if ($m['match']) {
				$key = substr($key,2,strlen($key)-4);
				$key = $this->getValue($key);
			}
			$result = $result . $key;
		}
		return $result;
	}
	function replaceParams($text,$eng,$encode=false) {
		$result = '';
		$mb = new CMatchBuilder();
		$mb->buildMatches($this->RGX_SUB, $text);
		for($idx=0; $idx<$mb->getCount(); $idx++) {
			$m = $mb->getMatch($idx);
			$key = $mb->getMatchText($m);
			if ($m['match']) {
				$key = substr($key,2,strlen($key)-4);
				$key = $this->getValue($key);
				if ($encode)
					$key = urlencode($key);
			}
			$result = $result . $key;
		}
		return $result;
	}		
	function replaceContentFields() {
		$this->setContent($this->replaceFields($this->getContent()));
	}
	function evalParamString($key) {
		return $this->getValue($key);
	}
	function libImport($libName) {
		global $KudzuLIB;
		$KudzuLIB->libImport($libName);
		$KudzuLIB->libSetTags($libName,$this);
	}
	function getLibrary() {
		global $KudzuLIB;
		return $KudzuLIB;
	}
	function setLibPath($libPath) {
		global $KudzuLIB;
		$KudzuLIB->setLibPath($libPath);
	}
	function isTrue($var) {
	  if($var) {
		if(is_bool($var)||is_int($var)||is_long($var)||is_float($var))
		  return TRUE;
		elseif(is_numeric($var))
		  return ((float)$var)?TRUE:FALSE;
		elseif(is_string($var))
		  return !in_array(strtolower($var),array("false","f","no","n"));
		elseif(is_object($var))
		  return ((Array)$var)?TRUE:FALSE;
		return TRUE;
	  }
	  return FALSE;
	}
	/* * * * BUILTIN TAG HANDLERS * * * */
	function tag_CASE ($node) {
		if (!$node->assertParamCount(1,'valName')) return;
		$value = $node->getParamItem(0);
		$value = $this->getValue($value);
		$child = $node->getChildNode($value);
		if ($node->evalChildNode($value))
			return;
		$node->evalChildNode('else');
	}
	function tag_CYCLE($node) {
		if (!$node->assertParamCount(1,'cycleIdx')) return;
		$cycleIdx = $node->getParamItem(0);
		if ($node->getParamCount()===1) {
			$this->putValue($cycleIdx,0);
			return;
			}
		if (!$node->assertParamCount(4,'cycleIdx|cycleTar|alt1|alt2[|altN]')) return;
		$cycleTar = $node->getParamItem(1);
		$idx = 0;
		if ($this->hasValue($cycleIdx))
			$idx = $this->getValue($cycleIdx)+1;
		if ($idx < 0 || $idx >= $node->getParamCount()-2)
			$idx = 0;
		$this->putValue($cycleIdx,$idx);
		$this->putValue($cycleTar,$node->getParamItem($idx+2));
	}
	function tag_FORARRAY($node) {
		if (!$node->assertParamCount(3,'value|fn|obj')) return;
		$pa = $this->evalParamString($node->getParamItem(0));
		$fn = $node->getParamItem(1);
		$ob = $this->evalParamString($node->getParamItem(2,null));
		if (!is_array($pa)) {
			$node->appendTagError('NotAnArray: '.$node->getParamItem(0));
			return;
		}
		$lst = count($pa)-1;
		$node->stackPush();
		for ($cur=0;$cur<=$lst;$cur++) {
			$val = $pa[$cur];
			if (is_null($ob)) {
				if (!$fn($node,$val,$cur===0,$cur===$lst)) break;
			} else {
				if(!call_user_func(array($ob,$fn),array($node,$val,$cur,$lst))) break;
			}
			$this->Helper_EvalNodes($node);
		}
		$node->stackPop();
	}
	function tag_FOREACH($node) {
		if (!$node->assertParamCount(3,'value|fn[|obj]')) return;
		$pa = $this->evalParamString($node->getParamItem(0));
		$fn = $node->getParamItem(1);
		$ob = $this->evalParamString($node->getParamItem(2,null));
		if (!is_array($pa)) {
			$node->appendTagError('NotAnArray: '.$node->getParamItem(0));
			return;
		}
		$cur = 0;
		$lst = count($pa)-1;
		$node->stackPush();
		foreach (array_keys($pa) as $key) {
			$val = $pa[$key];
			if (is_null($ob)) {
				if (!$fn($node,$val,$key,$cur===0,$cur===$lst))	break;
			} else {
				if(!call_user_func(array($ob,$fn),array($node,$val,$key,$cur,$lst))) break;
			}
			$this->Helper_EvalNodes($node);
			$cur += 1;
		}
		$node->stackPop();
	}
	function tag_ITERATE($node) {
		if (!$node->getParamCount(2,'obj|fn'))
			return;
		$ob = $node->evalParamString($node->getParamItem(0));
		$fn = $node->getParamItem(1);
		$cn = 0;
		$node->stackPush();		
		while (true) {
			if (is_null($ob)) {
				if (!$fn($node,$cn)) break;
			} else {
				if(!call_user_func(array($ob,$fn),array($node,$val,$key,$cur,$lst))) break;
			}
			$this->Helper_EvalNodes($node);
		}
		$node->stackPop();
	}
	function tag_IMPORT($node) {
		if (!$node->assertParamCount(1,'libName[|libName2]*')) return;
		for ($idx=0;$idx<$node->getParamCount();$idx++)
			$this->libImport($node->getParamItem($idx));
	}
	function tag_IFTHEN($node) {
		if (!$node->assertParamCount(1,'value')) return;
		$bool = $this->getValue($node->getParamItem(0,0));
		$this->Helper_BOOL($node,$bool);
	}
	function tag_IFTRUE($node) {
		if (!$node->assertParamCount(1,'value')) return;
		if (!$this->getValue($node->getParamItem(0))) return;
		$this->Helper_EvalNodes($node);
	}
	function tag_IFFALSE($node) {
		if (!$node->assertParamCount(1,'value')) return;
		if ($this->getValue($node->getParamItem(0))) return;
		$this->Helper_EvalNodes($node);
	}
	function tag_IGNORE($node) { }
	function tag_REPLACE($node) {
		if (!$node->assertParamCount(1,'value')) return;
		$strip = (strcasecmp($node->getParamItem(1,''),'true')===0);
		$node->stackPush();
		$v = $this->evalParamString($node->getParamItem(0));
		if ($strip)
			$v = strip_tags($v);
		$node->getEngine()->contentAppend($v);
		$node->stackPop();
	}
	function tag_SETVALUE($node) {
		if (!$node->assertParamCount(2,'key|value')) return;
		$eng = $node->getEngine();
		$eng->setValue($node->getParamItem(0),$node->getParamItem(1));
	}
	function tag_SUBST($node) {
		$node->stackPush();
		$node->evalNodes();
		$this->replaceContentFields();
		$node->stackPop();
	}
	function tag_RANDOM($node) {
		$arr = array();
		for($idx=0;$idx<$node->getNodeCount();$idx++) {
			if ($node->getNodeItem($idx)->getID()=='_txt_') 
				continue;
			array_push($arr,$node->getNodeItem($idx));
		}
		$jdx = count($arr);
		if ($jdx < 0) return;
		if ($jdx > 1)
			$jdx = rand(0,$jdx-1);
		$rnode = $arr[$jdx];
		$rnode->evalNodes();
	}
	/* * * * HELPER METHODS * * * */
	function Helper_BOOL($node,$bool) {
		$node->stackPush();
		$node->evalChildNode($bool ? 'Then' : 'Else');
		$node->stackPop();
	}
	function Helper_EvalNodes($node) {
		$node->stackPush();
		$node->evalNodes();
		$node->stackPop();
	}
}
?>