<?php

/**
 * XMLReader subclass that adds a few convenience methods
 * @package TEIShredder
 * @author Carsten Bluem <carsten@bluem.net>
 * @link https://github.com/TEIShredder/
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TEIShredder_XMLReader extends XMLReader {

	/**
	 * Returns the full open tag for the current element
	 * @return string Opening tag including attributes
	 * @throws InvalidArgumentException
	 */
	public function nodeOpenString() {
		if (XMLREADER::ELEMENT != $this->nodeType) {
			throw new InvalidArgumentException('This node is not an opening element.');
		}
		$str = $this->prefix.$this->localName;
		if ($this->hasAttributes) {
			$this->moveToFirstAttribute();
			do {
				$str .= ' '.($this->prefix ? $this->prefix.':' : '').
				        $this->localName.'="'.htmlspecialchars($this->value).'"';
			} while ($this->moveToNextAttribute());
			$this->moveToElement();
		}
		if ($this->isEmptyElement) {
			$str .= '/';
		}
		return "<$str>";
	}

}
