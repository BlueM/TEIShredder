XMLREADER ---------------------------------------------------------------------------

	/**
	 * @test
	 */
	function extractThePlaintextContentOfATag() {
		$this->reader->xml('<root>This is <hi>only</hi> dummy <graphic src="bla" /> text.</root>');
		while ($this->reader->read()) {
			if ('root' != $this->reader->localName or
			    XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('This is only dummy text.', $this->reader->extractPlaintextContent());
		}
	}

	/**
	 * @test
	 */
	function extractTheTitle() {
		$this->reader->xml('<root><p>Bla</p><div>Divdiv</div> '.
		                   '<head><em>My</em> Headline</head></root>');
		while ($this->reader->read()) {
			if (XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('My Headline', $this->reader->extractTitle());
			break;
		}
	}

	/**
	 * @test
	 */
	function extractingTheTitleWillReturnAnEmptyStringIfThereIsNoHeadTag() {
		$this->reader->xml('<root><p>Bla</p></root>');
		while ($this->reader->read()) {
			if (XMLReader::ELEMENT != $this->reader->nodeType) {
				continue;
			}
			$this->assertSame('', $this->reader->extractTitle());
			break;
		}
	}
