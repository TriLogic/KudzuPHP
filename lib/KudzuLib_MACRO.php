<?php
/*  
Module: KudzuLib_MACRO.php
Description: Kazoo library to allow template nodes to be used as macros.
Author: Andrew Friedl
Author URI: http://www.kazooplugin.com/
License: GPLv2 (see license.txt)
*/

// Library Class Definition
class CKudzuLibMACRO {
	function tag_MACRO($node) {
		if ( !$node->assertParamCount(2,"valName|exec") )
			return;
		$val = $node->getParamItem(0);
		$node->getEngine()->putValue($val,$node);
		$val = $node->getParamItem(1);
		if (strcasecmp($val,'true'))
			$node->getEngine()->Helper_EvalNodes($node);
	}
	function tag_REPLAY($node) {
		if ( !$node->assertParamCount(2,"valName") )
			return;
		$val = $node->getParamItem(0);
		$rnode = $node->getEngine()->getValue($val);
		$node->stackPush();
		$rnode->evalNodes();
		$node->stackPop();
	}
	function setTags($tagLib) {
		$tagLib->setTagFn('macro','tag_MACRO',$this);
		$tagLib->setTagFn('replay','tag_REPLAY',$this);
	}
}

// Library Tag Installation
function KudzuLibImport_MACRO($tagLib) {
	$obj = new CKudzuLibMACRO();
	$obj->setTags($tagLib);
}
?>