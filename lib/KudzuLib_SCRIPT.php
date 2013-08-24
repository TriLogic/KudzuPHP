<?php
/*  
Module: KudzuLib_SCRIPT.php
Description: Kudzu library to Templated PHP and Expressions.
Author: Andrew Friedl
Author URI: http://www.kazooplugin.com/
License: GPLv2 (see license.txt)
*/

// Library Class Definition
class CKudzuLibSCRIPT {
	function tag_INCLUDE($node) {
		if ( !$node->assertParamCount(1,"page") )
			return;
		$eng = $node->getEngine();
		$param = $node->getParamItem( 0 );
		$deref = strcasecmp( substr( $param, 0, 1 ), "$" ) == 0;
		if( $deref ) {
			$param = substr( $param, 1, strlen($param) - 1 );
			$param = $node->getEngine()->getValue( $param );
		}
		if ( is_null($param) ) {
			$node->appendTagError( "InvalidPageReference: " . $node->getParamItem( 0 ) );
			return;
		}
		$node->stackPush();
		ob_start();
		try {
			include($param);
			$eng->setContent(ob_get_clean());
		} catch ( Exception $e ) {
			$node->appendTagError( $e );
		}
		ob_end_clean();
		$node->stackPop();
	}
	function tag_EVAL($node) {
		$eng = $node->getEngine();
		$node->stackPush();
		$node->evalNodes();
		$code = $eng->getContent();
		$eng->setContent('');
		ob_start();
		$result = eval($code);
		$eng->setContent(ob_get_clean());
		ob_end_clean();
		$node->stackPop();
	}
	function setTags($tagLib) {
		$tagLib->setTagFn('eval','tag_EVAL',$this);
		$tagLib->setTagFn('include','tag_INCLUDE',$this);
	}
}

// Library Tag Installation
function KudzuLibImport_SCRIPT($lib) {
	$obj = new CKudzuLibSCRIPT();
	$obj->setTags($tagLib);
}
?>