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
 * This is the base library of HTML elements for use in XHP. This includes all
 * non-deprecated tags and attributes. Elements in this file should stay as
 * close to spec as possible. Facebook-specific extensions should go into their
 * own elements.
 */
class PXHP_HtmlElement extends PXHP_Primitive {
	
	/* NOTE: No attributes needed since it's not XML.
	
	// TODO: Break these out into abstract elements so that elements that need
	// them can steal the definition. Right now this is an overloaded list of
	// attributes.
	// attribute
	  // HTML attributes
	  string accesskey, string class, string dir, string id, string lang,
	  string style, string tabindex, string title,

	  // Javascript events
	  string onabort, string onblur, string onchange, string onclick,
	  string ondblclick, string onerror, string onfocus, string onkeydown,
	  string onkeypress, string onkeyup, string onload, string onmousedown,
	  string onmousemove, string onmouseout, string onmouseover, string onmouseup,
	  string onreset, string onresize, string onselect, string onsubmit,
	  string onunload,

	  // IE only
	  string onmouseenter, string onmouseleave,

	  // Joe Hewitt!!
	  // TODO:
	  string selected, string otherButtonLabel, string otherButtonHref,
	  string otherButtonClass, string type, string replaceCaret,
	  string replaceChildren;
	*/

	protected
		$tagName;

	public function requireUniqueId() {
		// TODO: Implement something on AsyncRequest that returns the number of
		//       requests sent so far so we can remove the microtime(true) thing.
		if (!($id = $this->getAttribute('id'))) {
			$this->setAttribute('id', $id = substr(md5(mt_rand(0, 100000)), 0, 10));
		}
		return $id;
	}

	protected final function renderBaseAttrs() {
		$buf = '<'.$this->tagName;
		foreach ($this->getAttributes() as $key => $val) {
			if ($val !== null && $val !== false) {
				$buf .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val, true) . '"';
			}
		}
		return $buf;
	}

	public function addClass($class) {
		$class = trim($class);

		$currentClasses = $this->getAttribute('class');
		$has = strpos(' '.$currentClasses.' ', ' '.$class.' ') !== false;
		if ($has) {
			return $this;
		}

		$this->setAttribute('class', trim($currentClasses.' '.$class));
		return $this;
	}

	protected function stringify() {
		$buf = $this->renderBaseAttrs() . '>';
		foreach ($this->getChildren() as $child) {
			$buf .= PXHP_Base::renderChild($child);
		}
		$buf .= '</'.$this->tagName.'>';
		return $buf;
	}
	
}

/**
 * Subclasses of PXHP_HtmlSingleton may not contain children. When rendered they
 * will be in singleton (<img />, <br />) form.
 */
class PXHP_HtmlSingleton extends PXHP_HtmlElement {
  
	//NOTE:  Will this be needed someday?
	//children empty;

	protected function stringify() {
		return $this->renderBaseAttrs() . ' />';
	}
}

/**
 * Subclasses of PXHP_PseudoSingleton may contain exactly zero or one
 * children. When rendered they will be in full open\close form, no matter how
 * many children there are.
 */
class PXHP_PseudoSingleton extends PXHP_HtmlElement {

	//NOTE:  Will this be needed someday?
	//children (pcdata)*;

	protected function escape($txt) {
		return htmlspecialchars($txt);
	}

	protected function stringify() {
		$buf = $this->renderBaseAttrs() . '>';
		if ($children = $this->getChildren()) {
			$buf .= PXHP_Base::renderChild($children[0]);
		}
		return $buf . '</'.$this->tagName.'>';
	}
}

/**
 * Below is a big wall of element definitions. These are the basic building
 * blocks of XHP pages.
 */
class PXHP_A extends PXHP_HtmlElement {
	
	// attribute
	// string href, string name, string rel, string target;
	// category %flow, %phrase, %interactive;
	// // transparent
	// // may not contain %interactive
	// children (pcdata | %flow)*;
	
	public $href;
	public $name;
	public $rel;
	public $target;
	
	protected $tagName = 'a';
	
}

class PXHP_abbr extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'abbr';
}

class PXHP_acronym extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'acronym';
}

class PXHP_address extends PXHP_HtmlElement {
	// category %flow;
	// may not contain h1-h6
	// children (pcdata | %flow)*;
	protected $tagName = 'address';
}

//NOTE:  Don't need this element, skip it for now.
// class PXHP_area extends PXHP_HtmlSingleton { 
// 	// attribute string alt, string coords, string href, bool nohref, string target;
// 	protected $tagName = 'area';
// }

class PXHP_b extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'b';
}

//NOTE:  Don't need this element, skip it for now.
// class PXHP_base extends PXHP_HtmlSingleton {
// 	// attribute string href, string target;
// 	// also a member of "metadata", but is not listed here. see comments in :head
// 	
// 	public $href;
// 	public $target;
// 	
// 	// for more information
// 	protected $tagName = 'base';
// }

class PXHP_big extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'big';
}

class PXHP_blockquote extends PXHP_HtmlElement {
	// attribute string cite;
	// category %flow;
	// children (pcdata | %flow)*;
	
	public $cite;
	
	protected $tagName = 'blockquote';
}

class PXHP_body extends PXHP_HtmlElement {
	// children (pcdata | %flow)*;
	protected $tagName = 'body';
}

class PXHP_br extends PXHP_HtmlSingleton {
	// category %flow, %phrase;
	protected $tagName = 'br';
}

class PXHP_button extends PXHP_HtmlElement {
	// attribute
	// bool disabled, string name, enum { "submit", "button", "reset" } type, string value;
	// category %flow, %phrase, %interactive;
	// may not contain interactive
	// children (pcdata | %phrase)*;
	
	public $disabled;
	public $name;
	public $type;
	public $value;
	
	protected $tagName = 'button';
}

class PXHP_caption extends PXHP_HtmlElement {
	// may not contain table
	// children (pcdata | %flow)*;
	protected $tagName = 'caption';
}

class PXHP_cite extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'cite';
}

class PXHP_code extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'code';
}

class PXHP_col extends PXHP_HtmlSingleton {
	// attribute
	// enum { "left", "right", "center", "justify", "char" } align, string char,
	// 	int charoff, int span,
	// 	enum { "top", "middle", "bottom", "baseline" } valign, string width;
	
	public $align;
	public $char;
	public $charoff;
	public $span;
	public $valign;
	public $width;
	
	protected $tagName = 'col';
}

class PXHP_colgroup extends PXHP_HtmlElement {
	// attribute
	// enum { "left", "right", "center", "justify", "char" } align, string char,
	// 	int charoff, int span,
	// 	enum { "top", "middle", "bottom", "baseline" } valign, string width;
	// children (:col)*;
	
	public $align;
	public $char;
	public $charoff;
	public $span;
	public $valign;
	public $width;
	
	protected $tagName = 'colgroup';
}

class PXHP_dd extends PXHP_HtmlElement {
	// children (pcdata | %flow)*;
	protected $tagName = 'dd';
}

class PXHP_del extends PXHP_HtmlElement {
	// attribute string cite, string datetime;
	// category %flow, %phrase;
	// transparent
	// children (pcdata | %flow)*;
	
	public $cite;
	public $datetime;
	
	protected $tagName = 'del';
}

class PXHP_div extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %flow)*;
	protected $tagName = 'div';
}

class PXHP_dfn extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'dfn';
}

class PXHP_dl extends PXHP_HtmlElement {
	// category %flow;
	// children (:dt+, :dd+)*;
	protected $tagName = 'dl';
}

class PXHP_dt extends PXHP_HtmlElement {
	// children (pcdata | %phrase)*;
	protected $tagName = 'dt';
}

class PXHP_em extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'em';
}

class PXHP_fieldset extends PXHP_HtmlElement {
	// category %flow;
	// children (:legend?, (pcdata | %flow)*);
	protected $tagName = 'fieldset';
}

class PXHP_form extends PXHP_HtmlElement {
	// attribute
	// string action, string accept, string accept-charset, string enctype,
	// enum { "get", "post" } method, string name, string target, bool ajaxify;
	// category %flow;
	// may not contain form
	// children (pcdata | %flow)*;
	
	public $action;
	public $accept;
	public $accept_charset;
	public $enctype;
	public $method;
	public $name;
	public $target;
	public $ajaxify;
	
	protected $tagName = 'form';
}

class PXHP_frame extends PXHP_HtmlSingleton {
	// attribute
	// bool frameborder, string longdesc, int marginheight, int marginwidth,
	// 	string name, bool noresize, enum { "yes", "no", "auto" } scrolling,
	// 	public $src;
	
	public $frameborder;
	public $longdesc;
	public $marginheight;
	public $marginwidth;
	public $name;
	public $noresize;
	public $scrolling;
	public $src;
	
	protected $tagName = 'frame';
}

class PXHP_frameset extends PXHP_HtmlElement {
	// children (:frame | :frameset | :noframes)*;
	protected $tagName = 'frameset';
}

class PXHP_h1 extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'h1';
}

class PXHP_h2 extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'h2';
}

class PXHP_h3 extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'h3';
}

class PXHP_h4 extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'h4';
}

class PXHP_h5 extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'h5';
}

class PXHP_h6 extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'h6';
}

class PXHP_head extends PXHP_HtmlElement {
	// attribute string profile;
	// children (%metadata*, :title, %metadata*, :base?, %metadata*);
	// Note: html/xhtml spec says that there should be exactly 1 <title />, and at
	// most 1 <base />. These elements can occur in any order, and can be
	// surrounded by any number of other elements (in %metadata). The problem
	// here is that XHP's validation does not backtrack, so there's no way to
	// accurately implement the spec. This is the closest we can get. The only
	// difference between this and the spec is that in XHP the <title /> must
	// appear before the <base />.
	
	public $profile;
	
	protected $tagName = 'head';
}

class PXHP_hr extends PXHP_HtmlSingleton {
	// category %flow;
	protected $tagName = 'hr';
}

class PXHP_html extends PXHP_HtmlElement {
	// attribute string xmlns;
	// children (:head, :body);
	
	public $xmlns;
	
	protected $tagName = 'html';
}

class PXHP_i extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'i';
}

class PXHP_iframe extends PXHP_PseudoSingleton {
	// attribute
	// enum {"1", "0"} frameborder,
	// 	string height, string longdesc, int marginheight,
	// 	int marginwidth, string name, enum { "yes", "no", "auto" } scrolling,
	// 	public $src, string width;
	// category %flow, %phrase, %interactive;
	// children empty;
	
	public $frameborder;
	public $height;
	public $longdesc;
	public $marginheight;
	public $marginwidth;
	public $name;
	public $scrolling;
	public $src;
	public $width;
	
	protected $tagName = 'iframe';
}

class PXHP_img extends PXHP_HtmlSingleton {
	// attribute
	// Lite
	// string staticsrc,
	// // HTML
	// string alt, string src, string height, bool ismap, string longdesc,
	// string usemap, string width;
	// category %flow, %phrase;
	
	public $alt;
	public $src;
	public $height;
	public $ismap;
	public $longdesc;
	public $usemap;
	public $width;
	
	protected $tagName = 'img';
}

class PXHP_input extends PXHP_HtmlSingleton {
	// attribute
	// Non-standard
	// enum { "on", "off" } autocomplete,
	// 	string placeholder,
	// 	// HTML
	// 	string accept, enum { "left", "right", "top", "middle", "bottom" } align,
	// 	string alt, bool checked, bool disabled, int maxlength, string name,
	// 	bool readonly, int size, string src,
	// 	enum {
	// 	"button", "checkbox", "file", "hidden", "image", "password", "radio",
	// 	"reset", "submit", "text"
	// 	} type,
	// 	public $value;
	// category %flow, %phrase, %interactive;
	
	// Non-standard
	public $autocomplete;
	public $placeholder;
	// HTML
	public $accept;
	public $align;
	public $alt;
	public $checked;
	public $disabled;
	public $maxlength;
	public $name;
	public $readonly;
	public $size;
	public $src;
	public $type;
	public $value;
	
	protected $tagName = 'input';
}

class PXHP_ins extends PXHP_HtmlElement {
	// attribute string cite, string datetime;
	// category %flow, %phrase;
	// transparent
	// children (pcdata | %flow)*;
	
	public $cite;
	public $datetime;
	
	protected $tagName = 'ins';
}

class PXHP_kbd extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'kbd';
}

class PXHP_label extends PXHP_HtmlElement {
	// attribute string for;
	// category %flow, %phrase, %interactive;
	// may not contain label
	// children (pcdata | %phrase)*;
	
	public $for;
	
	protected $tagName = 'label';
}

class PXHP_legend extends PXHP_HtmlElement {
	// children (pcdata | %phrase)*;
	protected $tagName = 'legend';
}

class PXHP_li extends PXHP_HtmlElement {
	// children (pcdata | %flow)*;
	protected $tagName = 'li';
}

class PXHP_link extends PXHP_HtmlSingleton {
	// attribute
	// string charset, string href, string hreflang, string media, string rel,
	// 	public $rev, string target, string type;
	// category %metadata;
	
	public $charset;
	public $href;
	public $hreflang;
	public $media;
	public $rel;
	public $rev;
	public $target;
	public $type;
	
	protected $tagName = 'link';
}

class PXHP_map extends PXHP_HtmlElement {
	// attribute string name;
	// category %flow, %phrase;
	// transparent
	// children ((pcdata | %flow)+ | :area+);
	
	public $name;
	
	protected $tagName = 'map';
}

class PXHP_meta extends PXHP_HtmlSingleton {
	// attribute
	// string content @required,
	// 	enum {
	// 	"content-type", "content-style-type", "expires", "refresh", "set-cookie"
	// 	} http-equiv,
	// 	public $http-equiv, string name, string scheme;
	// category %metadata;
	
	public $content;
	public $http_equiv;
	public $name;
	public $scheme;
	
	protected $tagName = 'meta';
}

class PXHP_noframes extends PXHP_HtmlElement {
	// children (%html-body);
	protected $tagName = 'noframes';
}

class PXHP_noscript extends PXHP_HtmlElement {
	// transparent
	// category %flow, %phrase;
	protected $tagName = 'noscript';
}

class PXHP_object extends PXHP_HtmlElement {
	// attribute
	// enum { "left", "right", "top", "bottom" } align, string archive, int border,
	// 	string classid, string codebase, string codetype, string data, bool declare,
	// 	int height, int hspace, string name, string standby, string type,
	// 	public $usemap, int vspace, int width;
	// category %flow, %phrase;
	// transparent, after the params
	// children (:param*, (pcdata | %flow)*);
	
	public $align;
	public $archive;
	public $border;
	public $classid;
	public $codebase;
	public $codetype;
	public $data;
	public $declare;
	public $height;
	public $hspace;
	public $name;
	public $standby;
	public $type;
	public $usemap;
	public $vspace;
	public $width;
	
	protected $tagName = 'object';
}

class PXHP_ol extends PXHP_HtmlElement {
	// category %flow;
	// children (:li)*;
	protected $tagName = 'ol';
}

class PXHP_optgroup extends PXHP_HtmlElement {
	// attribute string label, bool disabled;
	// children (:option)*;
	
	public $label;
	public $disabled;
	
	protected $tagName = 'optgroup';
}

class PXHP_option extends PXHP_PseudoSingleton {
	// attribute bool disabled, string label, bool selected, string value;
	protected $tagName = 'option';
}

class PXHP_p extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'p';
}

class PXHP_param extends PXHP_PseudoSingleton {
	// attribute
	// string name, string type, string value,
	// enum { "data", "ref", "object" } valuetype;
	
	public $name;
	public $type;
	public $value;
	public $valuetype;
	
	protected $tagName = 'param';
}

class PXHP_pre extends PXHP_HtmlElement {
	// category %flow;
	// children (pcdata | %phrase)*;
	protected $tagName = 'pre';
}

class PXHP_q extends PXHP_HtmlElement {
	// attribute string cite;
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	
	public $cite;
	
	protected $tagName = 'q';
}

//NOTE:  This is deprecated, ignore it.
// deprecated
// class :s extends PXHP_HtmlElement {
// category %flow, %phrase;
// children (pcdata | %phrase)*;
// protected $tagName = 's';
// }

class PXHP_samp extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'samp';
}

class PXHP_script extends PXHP_PseudoSingleton {
	// attribute string charset, bool defer, string src, string type;
	// category %flow, %phrase, %metadata;
	
	public $charset;
	public $defer;
	public $src;
	public $type;
	
	protected $tagName = 'script';
}

class PXHP_select extends PXHP_HtmlElement {
	// attribute bool disabled, bool multiple, string name, int size;
	// category %flow, %phrase, %interactive;
	// children (:option | :optgroup)*;
	
	public $disabled;
	public $multiple;
	public $name;
	public $size;
	
	protected $tagName = 'select';
}

class PXHP_small extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'small';
}

class PXHP_span extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'span';
}

class PXHP_strong extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'strong';
}

class PXHP_style extends PXHP_PseudoSingleton {
	// attribute
	// enum {
	// 	"screen", "tty", "tv", "projection", "handheld", "print", "braille",
	// 	"aural", "all"
	// 	} media, string type;
	// category %metadata;
	
	public $media;
	public $type;
	
	protected $tagName = 'style';
}

class PXHP_sub extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase);
	protected $tagName = 'sub';
}

class PXHP_sup extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase);
	protected $tagName = 'sup';
}

class PXHP_table extends PXHP_HtmlElement {
	// attribute
	// int border, int cellpadding, int cellspacing,
	// 	enum {
	// 	"void", "above", "below", "hsides", "lhs", "rhs", "vsides", "box",
	// 	"border"
	// 	} frame,
	// 	enum { "none", "groups", "rows", "cols", "all" } rules,
	// 	public $summary, string width;
	// category %flow;
	// children (
	// :caption?, :colgroup*,
	// :thead?,
	// (
	// (:tfoot, (:tbody+ | :tr*)) |
	// ((:tbody+ | :tr*), :tfoot?)
	// )
	// );
	
	public $border;
	public $cellpadding;
	public $cellspacing;
	public $frame;
	public $rules;
	public $summary;
	public $width;
	
	protected $tagName = 'table';
}

class PXHP_tbody extends PXHP_HtmlElement {
	// attribute
	// enum { "right", "left", "center", "justify", "char" } align, string char,
	// 	public $charoff, enum { "top", "middle", "bottom", "baseline" } valign;
	// children (:tr)*;
	
	public $align;
	public $char;
	public $charoff;
	public $valign;
	
	protected $tagName = 'tbody';
}


class PXHP_td extends PXHP_HtmlElement {
	// attribute
	// string abbr, enum { "left", "right", "center", "justify", "char" } align,
	// 	string axis, string char, int charoff, int colspan, string headers,
	// 	int rowspan, enum { "col", "colgroup", "row", "rowgroup" } scope,
	// 	enum { "top", "middle", "bottom", "baseline" } valign;
	// children (pcdata | %flow)*;
	
	public $abbr;
 	public $align;
	public $axis;
	public $char;
	public $charoff;
	public $colspan;
	public $headers;
	public $rowspan;
 	public $scope;
	public $valign;
	
	protected $tagName = 'td';
}

class PXHP_textarea extends PXHP_PseudoSingleton {
	// attribute int cols, int rows, bool disabled, string name, bool readonly;
	// category %flow, %phrase, %interactive;
	
	public $cols;
	public $rows;
	public $disabled;
	public $name;
	public $readonly;
	
	protected $tagName = 'textarea';
}

class PXHP_tfoot extends PXHP_HtmlElement {
	// attribute
	// enum { "left", "right", "center", "justify", "char" } align, string char,
	// 	public $charoff, enum { "top", "middle", "bottom", "baseline" } valign;
	// children (:tr)*;
	
	public $align;
	public $char;
	public $charoff;
	public $valign;
	
	protected $tagName = 'tfoot';
}

class PXHP_th extends PXHP_HtmlElement {
	// attribute
	// string abbr, enum { "left", "right", "center", "justify", "char" } align,
	// 	string axis, string char, int charoff, int colspan, int rowspan,
	// 	enum { "col", "colgroup", "row", "rowgroup" } scope,
	// 	enum { "top", "middle", "bottom", "baseline" } valign;
	// children (pcdata | %flow)*;
	
	public $abbr;
 	public $align;
	public $axis;
	public $char;
	public $charoff;
	public $colspan;
	public $rowspan;
	public $scope;
	public $valign;
	
	protected $tagName = 'th';
}

class PXHP_thead extends PXHP_HtmlElement {
	// attribute
	// enum { "left", "right", "center", "justify", "char" } align, string char,
	// 	public $charoff, enum { "top", "middle", "bottom", "baseline" } valign;
	// children (:tr)*;
	
	public $align;
	public $char;
	public $charoff;
	public $valign;
	
	protected $tagName = 'thead';
}

class PXHP_title extends PXHP_PseudoSingleton {
	// also a member of "metadata", but is not listed here. see comments in :head
	// for more information.
	protected $tagName = 'title';
}

class PXHP_tr extends PXHP_HtmlElement {
	// attribute
	// enum { "left", "right", "center", "justify", "char" } align, string char,
	// 	public $charoff, enum { "top", "middle", "bottom", "baseline" } valign;
	// children (:th | :td)*;
	
	public $align;
	public $char;
	public $charoff;
	public $valign;
	
	protected $tagName = 'tr';
}

class PXHP_tt extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'tt';
}

//NOTE:  This is deprecated, ignore it.
// deprecated
// class :u extends PXHP_HtmlElement {
// category %flow, %phrase;
// children (pcdata | %phrase)*;
// protected $tagName = 'u';
// }

class PXHP_ul extends PXHP_HtmlElement {
	// category %flow;
	// children (:li)*;
	protected $tagName = 'ul';
}

class PXHP_var extends PXHP_HtmlElement {
	// category %flow, %phrase;
	// children (pcdata | %phrase)*;
	protected $tagName = 'var';
}

/**
 * Render an <html /> element with a DOCTYPE, great for dumping a page to a
 * browser. Choose from a wide variety of flavors like XHTML 1.0 Strict, HTML
 * 4.01 Transitional, and new and improved HTML 5!
 *
 * Note: Some flavors may not be available in your area.
 */
class PXHP_doctype extends PXHP_Primitive {
	
	// children (:html);

	protected function stringify() {
		$children = $this->getChildren();
		return '<!DOCTYPE html>' . (PXHP_Base::renderChild($children[0]));
	}

}

?>