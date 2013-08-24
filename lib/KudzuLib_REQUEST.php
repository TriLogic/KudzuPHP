<?php
/*  
Module: KudzuLib_REQUEST.php
Description: Kudzu Extension Library for handling Server Request Vars
Author: Andrew Friedl
Author URI: http://www.kazooplugin.com/
License: GPLv2 (see license.txt)
*/

// Library Class Definition
class CKudzuLibREQUEST {
	function tag_REQUEST_REQUEST_TYPE($node,$evalChild=true) {
		$eng = $node->getEngine();
		$tmp = $_SERVER['REQUEST_METHOD'];
		$eng->putValue('request_method', $tmp);
		$eng->putValue('is_head_request', strcmp($tmp,'HEAD')===0);
		$eng->putValue('is_post_request', strcmp($tmp,'POST')===0);
		$eng->putValue('is_get_request', strcmp($tmp,'GET')===0);
		$eng->putValue('is_put_request', strcmp($tmp,'PUT')===0);
		if ( $evalChild )
			$node->evalNodes();
	}
	function tag_REQUEST_SERVER_VARS($node,$evalChild=true) {
		$this->tag_REQUEST_REQUEST_TYPE($node,false);
		$eng = $node->getEngine();
		foreach($_SERVER as $key => $val) {
			$eng->putValue($key,$val);
		}
		if ( $evalChild )
			$node->evalNodes();
	}
	function tag_REQUEST_GET_VARS($node) {
		$this->tag_REQUEST_SERVER_VARS($node,false);
		$eng = $node->getEngine();
		foreach($_GET as $key => $value) {
			$eng->putValue($key,htmlspecialchars($value));
		}
		$node->evalNodes();
	}
	function tag_REQUEST_POST_VARS($node) {
		$this->tag_REQUEST_SERVER_VARS($node,false);
		$eng = $node->getEngine();
		foreach($_POST as $key => $value) {
			$eng->putValue($key,htmlspecialchars($value));
		}
		$node->evalNodes();
	}
	function tag_REQUEST_FOREACH_SERVER($node) {
		$this->tag_REQUEST_SERVER_VARS($node,false);
		$eng = $node->getEngine();
		$node->evalChildNode('Header');
		$orig = (strcasecmp($node->getParamItem(0,'false'),true)===0);
		foreach($_SERVER as $key => $val) {
			if ( strpos($key,'ORIG_') === 0 )
				if ( ! $orig ) continue;
			$eng->putValue('SERVER_VAR',$key);		
			$eng->putValue('SERVER_VAL',$val);
			$node->stackPush();
			$node->evalChildNode('Item');
			$node->stackPop();
		}
		$node->evalChildNode('Footer');
	}

	function tag_REQUEST_FOREACH_GET($node) {
		$this->tag_REQUEST_SERVER_VARS($node,false);
		$eng = $node->getEngine();
		$eng->putValue('get_count',count($_GET));
		$node->evalChildNode('Header');
		if ( count($_GET) < 1 ) {
			$node->evalChildNode('Empty');
		} else {
			foreach($_GET as $key => $val) {
				$eng->putValue('GET_VAR',$key);		
				$eng->putValue('GET_VAL',htmlspecialchars($val));
				$node->stackPush();
				$node->evalChildNode('Item');
				$node->stackPop();
			}
		}
		$node->evalChildNode('Footer');
	}
	function tag_REQUEST_FOREACH_POST($node) {
		$this->tag_REQUEST_SERVER_VARS($node,false);
		$eng = $node->getEngine();
		$eng->putValue('get_count',count($_POST));
		$node->evalChildNode('Header');
		if ( count($_POST) < 1 ) {
			$node->evalChildNode('Empty');
		} else {
			foreach($_POST as $key => $val) {
				$eng->putValue('POST_VAR',$key);		
				$eng->putValue('POST_VAL',htmlspecialchars($val));
				$node->stackPush();
				$node->evalChildNode('Item');
				$node->stackPop();
			}
		}
		$node->evalChildNode('Footer');
	}
	function setTags($tagLib) {
		$tagLib->setTagFn('request_type'  , 'tag_REQUEST_REQUEST_TYPE',$this);
		$tagLib->setTagFn('server_vars'   , 'tag_REQUEST_SERVER_VARS',$this);
		$tagLib->setTagFn('get_vars'      , 'tag_REQUEST_GET_VARS',$this);
		$tagLib->setTagFn('post_vars'     , 'tag_REQUEST_POST_VARS',$this);
		$tagLib->setTagFn('foreach_server', 'tag_REQUEST_FOREACH_SERVER',$this);
		$tagLib->setTagFn('foreach_get'   , 'tag_REQUEST_FOREACH_GET',$this);
		$tagLib->setTagFn('foreach_post'  , 'tag_REQUEST_FOREACH_POST',$this);
	}
}

// Library Tag Installation
function KudzuLibImport_REQUEST($tagLib) {
	$obj = new CKudzuLibREQUEST();
	$obj->setTags($tagLib);
}
?>