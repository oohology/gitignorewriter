<?php

use GitIgnoreWriter\GitIgnoreWriter;

class GitIgnoreWriterTest extends PHPUnit_Framework_TestCase
{

    public $writer;

    public function setUp() {
        $this->writer = new GitIgnoreWriter;
    }

    public function testLoad() {
        $result = $this->writer->load(__DIR__.'/fixtures/gitignore.example');
        $this->assertInstanceOf(GitIgnoreWriter\GitIgnoreWriter::class, $result);
    }

    public function testLoadException() {
        $this->expectException(\Exception::class);
        $this->writer->load(__DIR__.'/fixtures/nonexistant.example');
    }

}