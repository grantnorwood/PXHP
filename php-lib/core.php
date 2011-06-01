<?php

/*
  +----------------------------------------------------------------------+
  | PXHP (Pseudo-XHP)                                                    |
  | https://github.com/grantnorwood/pxhp                                 |
  +----------------------------------------------------------------------+
  | Copyright (c) 2011 Grant K Norwood. (http://grantnorwood.com)        |
  +----------------------------------------------------------------------+
  |                                                                      |
  | PXHP is a PHP-only library for building and rendering UI widgets,    |
  | but without the XML stuff that XHP uses.  This makes it easier to    |
  | install and use without building/making/installing any extensions,   |
  | even in shared hosting environments.  Also, XHP isn't available on   |
  | Mac or PC quite yet, so this will have to do for those of us who     |
  | don't do all of our development in a Linux environment.  Facebook    |
  | did an amazing job with XHP, it's just not currently available       |
  | to everybody!                                                        |
  |                                                                      |
  | The PXHP project is based on the open-source XHP project by          |
  | Facebook, located at https://github.com/facebook/xhp.                |
  |                                                                      |
  | XHP is Copyright (c) 2009 - 2010 Facebook, Inc.                      |
  | (http://www.facebook.com)                                            |
  |                                                                      |
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE.PHP, and is    |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
*/

/**
 * The base class.
 */
class PXHP_Base {

	public function __construct() {}
	public function appendChild($child) {}
	public function getAttribute($attr) {}
	public function setAttribute($attr, $val) {}
	public function categoryOf($cat) {}
	public function __toString() {}
	
	/**
	 * Enabling validation will give you stricter documents; you won't be able to
	 * do many things that violate the XHTML 1.0 Strict spec. It is recommend that
	 * you leave this on because otherwise things like the `children` keyword will
	 * do nothing. This validation comes at some CPU cost, however, so if you are
	 * running a high-traffic site you will probably want to disable this in
	 * production. You should still leave it on while developing new features,
	 * though.
	 */
	public static $ENABLE_VALIDATION = false;  //XHP defaults to true, but we're not quite there yet.
	
	final protected static function renderChild($child) {
		if ($child instanceof PXHP_Base) {
			return $child->__toString();
		/* //No HTML at the moment ... maybe later.
		} else if ($child instanceof HTML) {
			return $child->render();*/
		} else if (is_array($child)) {
			throw new XHPRenderArrayException('Can not render array!');
		} else {
			return htmlspecialchars((string)$child);
		}
	}

}

/**
 * 
 */
class PXHP_ComposableElement extends PXHP_Base {
	private
		$attributes,
		$children;

	// Private constants indicating the declared types of attributes
	const TYPE_STRING = 1;
	const TYPE_BOOL   = 2;
	const TYPE_NUMBER = 3;
	const TYPE_ARRAY  = 4;
	const TYPE_OBJECT = 5;
	const TYPE_VAR    = 6;
	const TYPE_ENUM   = 7;
	const TYPE_FLOAT  = 8;

	protected function init() {}

	/**
	 * A new PXHP_ComposableElement is instantiated for every literal tag
	 * expression in the script.
	 *
	 * The following code:
	 * $foo = <foo attr="val">bar</foo>;
	 *
	 * will execute something like:
	 * $foo = new xhp_foo(array('attr' => 'val'), array('bar'));
	 *
	 * @param $attributes    map of attributes to values
	 * @param $children      list of children
	 */
	final public function __construct($attributes = array(), $children = array()) {
		
		//TODO: Validate attributes.
		// if ($attributes) {
		// 	foreach ($attributes as $key => &$val) {
		// 		$this->validateAttributeValue($key, $val);
		// 	}
		// 	unset($val);
		// }
		
		$this->attributes = $attributes;
		$this->children = array();
		foreach ($children as $child) {
			$this->appendChild($child);
		}
		
		//NOTE: No validation since it's not XML.
		// if (PXHP_Base::$ENABLE_VALIDATION) {
		// 	// There is some cost to having defaulted unused arguments on a function
		// 	// so we leave these out and get them with func_get_args().
		// 	$args = func_get_args();
		// 	if (isset($args[2])) {
		// 		$this->source = "$args[2]:$args[3]";
		// 	} else {
		// 		$this->source =
		// 			'You have ENABLE_VALIDATION on, but debug information is not being ' .
		// 			'passed to XHP objects correctly. Ensure xhp.include_debug is on ' .
		// 			'in your PHP configuration. Without this option enabled, ' .
		// 			'validation errors will be painful to debug at best.';
		// 	}
		// }
		
		$this->init();
	}

	/**
	 * Adds a child to the end of this node. If you give an array to this method
	 * then it will behave like a DocumentFragment.
	 *
	 * @param $child     single child or array of children
	 */
	final public function appendChild($child) {
		if (is_array($child)) {
			foreach ($child as $c) {
				$this->appendChild($c);
			}
		//TODO: Frag not yet implemented.
		// } else if ($child instanceof :x:frag) {
		// 	$this->children = array_merge($this->children, $child->children);
		} else if ($child !== null) {
			$this->children[] = $child;
		}
		return $this;
	}

	/**
	 * Fetches all direct children of this element that match a particular tag
	 * name (or all children if no tag is given)
	 *
	 * @param $tag_name   tag name (optional)
	 * @return array
	 */
	final protected function getChildren($tag_name = null) {
		if (!$tag_name) {
			return $this->children;
		}
		
		throw new PXHPException($this, '$tag_name arg is not net implemented!');
		
		//TODO: Allow returning only certain children.
		// $tag_name = PXHP_Base::element2class($tag_name);
		// $ret = array();
		// foreach ($this->children as $child) {
		// 	if ($child instanceof $tag_name) {
		// 		$ret[] = $child;
		// 	}
		// }
		// return $ret;
		
	}

	/**
	 * Fetches an attribute from this elements attribute store. If $attr is not
	 * defined in the store, and $default is null an exception will be thrown. An
	 * exception will also be thrown if $attr is not supported -- see
	 * `supportedAttributes`
	 *
	 * @param $attr      attribute to fetch
	 * @return           value
	 */
	final public function getAttribute($attr) {

		// Return attribute if it's there, otherwise default or exception.
		if (isset($this->attributes[$attr])) {
			return $this->attributes[$attr];
		}
		
		//NOTE:  Probably don't need all this below since it's not XML.
		// // Get the declaration on miss
		// $decl = $this->__xhpAttributeDeclaration();
		// 
		// if (!isset($decl[$attr])) {
		// 	throw new XHPAttributeNotSupportedException($this, $attr);
		// } else if (!empty($decl[$attr][3])) {
		// 	throw new XHPAttributeRequiredException($this, $attr);
		// } else {
		// 	$decl = $this->__xhpAttributeDeclaration();
		// 	return $decl[$attr][2];
		// }
		
	}

	final protected function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Sets an attribute in this element's attribute store. An exception will be
	 * thrown if $attr is not supported -- see `supportedAttributes`.
	 *
	 * @param $attr      attribute to set
	 * @param $val       value
	 */
	final public function setAttribute($attr, $val) {
		
		//TODO: Validate attribute value.
		// $this->validateAttributeValue($attr, $val);
		
		$this->attributes[$attr] = $val;
		return $this;
	}

}

/**
 * PXHP_Primitive lays down the foundation for very low-level elements. You
 * should directly PXHP_Primitive only if you are creating a core element that
 * needs to directly implement stringify(). All other elements should subclass
 * from PXHP_Element.
 */
class PXHP_Primitive extends PXHP_ComposableElement {
	protected function stringify() {}

	/**
	 *  This isn't __toString() because throwing an exception out of __toString()
	 *  produces a useless, immediate fatal, and allowing XHP to seamlessly cast
	 *  into strings encourages bad practices, like this real snippet:
	 *
	 *    $links .= <a>...</a>;
	 *    $links .= <a>...</a>;
	 *    return HTML($links);
	 *
	 */
	final public function __toString() {
		
		//TODO: Validate children.
		// // Validate our children
		// $this->__flushElementChildren();
		// if (PXHP_Base::$ENABLE_VALIDATION) {
		// 	$this->validateChildren();
		// }

		// Render to string
		return $this->stringify();
	}
	
	/**
	 * Add a render function to actually echo out from __toString();
	 */
	final public function render() {
		
		// Render to string
		echo $this->__toString();
		
	}
	
}

/**
* PXHP_Element defines an interface that all user-land elements should subclass
* from. The main difference between PXHP_Element and PXHP_Primitive is that
* subclasses of PXHP_Element should implement `render()` instead of `stringify`.
* This is important because most elements should not be dealing with strings
* of markup.
*/
class PXHP_Element extends PXHP_ComposableElement {
	final public function __toString() {
		$that = $this;

		if (PXHP_Base::$ENABLE_VALIDATION) {
			
			throw new PXHPException($this, "Child validation is not yet implemented!");
			
			//TODO: Validate children.
			// // Validate the current object
			// $that->validateChildren();
			// 
			// // And each intermediary object it returns
			// while (($that = $that->render()) instanceof PXHP_Element) {
			// 	$that->validateChildren();
			// }
			// 
			// // render() must always return XHPPrimitives
			// if (!($that instanceof PXHP_ComposableElement)) {
			// 	throw new XHPCoreRenderException($this, $that);
			// }
		
		} else {
			// Skip the above checks when not validating
			while (($that = $that->render()) instanceof PXHP_Element);
		}

		return $that->__toString();
	}
}








/**
 * The base PXHP exception.
 */
class PXHPException extends Exception {
	protected static function getElementName($that) {
		$name = get_class($that);
		if (substr($name, 0, 5) !== 'PXHP_') {
			return $name;
		} else {
			return "PXHP_SubClass_" . $name; 
			
			//NOTE: 
			//
			//This may actually need to return the child class 
			//name, like the original fb code does below.  But 
			//for now the above should be fine.
			//
			//PXHP_Base::class2element($name);
			
		}
	}
}

/**
 * Handle exceptions in PXHP classes.
 */
class PXHPClassException extends PXHPException {
	public function __construct($that, $msg) {
		parent::__construct(
			'Exception in class `' . PXHPException::getElementName($that) . "`\n\n".
			"$that->source\n\n".
			$msg
		);
	}
}

/**
 * DISABLED:  These are the exceptions that XHP implements, we may need these soon.
 */
/*
class XHPCoreRenderException extends XHPException {
  public function __construct($that, $rend) {
    parent::__construct(
      'PXHP_Element::render must reduce an object to an PXHP_Primitive, but `'.
      PXHP_Base::class2element(get_class($that)).'` reduced into `'.gettype($rend)."`.\n\n".
      $that->source
    );
  }
}
*/

class XHPRenderArrayException extends PXHPException {
}

/*
class XHPAttributeNotSupportedException extends XHPException {
  public function __construct($that, $attr) {
    parent::__construct(
      'Attribute `'.$attr.'` is not supported in class '.
      '`'.XHPException::getElementName($that)."`.\n\n".
      "$that->source\n\n".
      'Please check for typos in your attribute. If you are creating a new '.
      'attribute on this element please add your attribute to the '.
      "`supportedAttributes` method.\n\n"
    );
  }
}

class XHPAttributeRequiredException extends XHPException {
  public function __construct($that, $attr) {
    parent::__construct(
      'Required attribute `'.$attr.'` was not specified in element '.
      '`'.XHPException::getElementName($that)."`.\n\n".
      $that->source
    );
  }
}

class XHPInvalidAttributeException extends XHPException {
  public function __construct($that, $type, $attr, $val) {
    if (is_object($val)) {
      $val_type = get_class($val);
    } else {
      $val_type = gettype($val);
    }
    parent::__construct(
      "Invalid attribute `$attr` of type `$val_type` supplied to element `".
      PXHP_Base::class2element(get_class($that))."`, expected `$type`.\n\n".
      $that->source
    );
  }
}

class XHPInvalidChildrenException extends XHPException {
  public function __construct($that, $index) {
    parent::__construct(
      'Element `'.XHPException::getElementName($that).'` was rendered with '.
      "invalid children.\n\n".
      "$that->source\n\n".
      "Verified $index children before failing.\n\n".
      "Children expected:\n".$that->__getChildrenDeclaration()."\n\n".
      "Children received:\n".$that->__getChildrenDescription()
    );
  }
}
*/

?>