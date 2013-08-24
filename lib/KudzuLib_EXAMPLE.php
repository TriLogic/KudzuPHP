<?php
/*  
Module: KudzuLib_EXAMPLE.php
Description: An example on how to write a Kudzu Extension Library
Author: Andrew Friedl
Author URI: http://www.kazooplugin.com/
License: GPLv2 (see license.txt)
*/

// Library Class Definition
class CKudzuLibEXAMPLE {
	function tag_UCASE($node) {
		$eng = $node->getEngine();
		$node->stackPush();
		$node->evalNodes();
		$eng->setContent(strtoupper($eng->getContent()));
		$node->stackPop();
	}
	function tag_LCASE($node) {
		$eng = $node->getEngine();
		$node->stackPush();
		$node->evalNodes();
		$eng->setContent(strtolower($eng->getContent()));
		$node->stackPop();
	}
	function setTags($tagLib) {
		$tagLib->setTagFn('ucase','tag_UCASE',$this);
		$tagLib->setTagFn('lcase','tag_LCASE',$this);
	}
}

// Library Tag Installation
function KudzuLibImport_EXAMPLE($tagLib) {
	$obj = new CKudzuLibEXAMPLE();
	$obj->setTags($tagLib);
}
?>