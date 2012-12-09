<?php

namespace TEIShredder;

require_once __DIR__.'/../bootstrap.php';

/**
 * Unit tests for TEIShredder\PlaintextConverter
 *
 * @package    TEIShredder
 * @subpackage Tests
 * @covers     TEIShredder\PlaintextConverter
 */
class PlaintextConverterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function plaintextConversionWorks()
    {
        $pc = new PlaintextConverter;
        $actual = $pc->convert('<p>The characters &lt; &gt; &amp; <em>must</em> be converted.</p>');
        $this->assertSame('The characters < > & must be converted.', $actual);
    }
}
