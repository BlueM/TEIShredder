<?php

/**
 * XMLReader subclass which adds a convenience method.
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/BlueM/TEIShredder
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TEIShredder_XMLReader extends XMLReader {

	/**
	 * Returns the full open tag for the current element
	 * @param bool $skipxmlid [optional] Should @xml:id attributes be suppressed?
	 * @return string Opening tag including attributes
	 * @throws RuntimeException
	 */
	public function nodeOpenString($skipxmlid = false) {
		if (XMLREADER::ELEMENT != $this->nodeType) {
			throw new RuntimeException('This node is not an opening element.');
		}
		$str = $this->prefix.$this->localName;
		if ($this->hasAttributes) {
			$this->moveToFirstAttribute();
			do {
				$attr = ($this->prefix ? $this->prefix.':' : '').$this->localName;
				if ($skipxmlid and 'xml:id' == $skipxmlid) {
					continue;
				}
				$str .= ' '.$attr.'="'.htmlspecialchars($this->value).'"';
			} while ($this->moveToNextAttribute());
			$this->moveToElement();
		}
		if ($this->isEmptyElement) {
			$str .= '/';
		}
		return "<$str>";
	}

}
