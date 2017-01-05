<?php

use GitIgnoreWriter\GitIgnoreWriter;

class GitIgnoreWriterTest extends PHPUnit_Framework_TestCase
{

    protected $fixtures;

    public function setUp() {

        $this->fixtures = [
            'input' => 'gitignore.example',
            'output' => 'gitignore.output',
            'nonexistent' => 'nonexistent.example',
            'appendSingle' => 'gitignore.appendSingle',
            'appendMultiline' => 'gitignore.appendMultiline',
            'appendArray' => 'gitignore.appendArray',
            'insertBefore' => 'gitignore.before',
            'insertAfter' => 'gitignore.after',
        ];
    }

    protected function getFixturePath($key)
    {
        if(!isset($this->fixtures[$key])) {
            throw new \Exception('invalid fixture');
        }
        return __DIR__.'/fixtures/'.$this->fixtures[$key];
    }

    protected function tearDown()
    {
        $this->cleanup();
    }

    protected function cleanup()
    {
        if (is_file($this->getFixturePath('output'))) {
            unlink($this->getFixturePath('output'));
        }
    }

    public function testConstructWithPath()
    {
        $writer = new GitIgnoreWriter($this->getFixturePath('input'));
        $this->assertCount(8, $writer->toArray());
    }

    public function testLoad()
    {
        $writer = (new GitIgnoreWriter)->load($this->getFixturePath('input'));
        $this->assertInstanceOf(GitIgnoreWriter::class, $writer);

        return $writer;
    }

    public function testLoadException()
    {
        $this->expectException(\Exception::class);
        (new GitIgnoreWriter)->load($this->getFixturePath('nonexistent'));
    }

    /**
     * @depends testLoad
     * @dataProvider existenceDataProvider
     */
    public function testExists($value, $writer)
    {
        $this->assertTrue($writer->exists($value));
    }

    public function existenceDataProvider()
    {
        return [
            ['vendor/'],
            ['databases/*.sql'],
            ['._*'],
        ];
    }

    /**
     * @depends testLoad
     * @dataProvider nonExistenceDataProvider
     */
    public function testNotExists($value, $writer)
    {
        $this->assertFalse($writer->exists($value));
    }

    public function nonExistenceDataProvider()
    {
        return [
            ['vendor'],
            ['*.sql'],
            ['whatever'],
        ];
    }

    /**
     * @depends clone testLoad
     */
    public function testAppendSingle($writer)
    {
        $writer
            ->add('a/Single/Path')
            ->save($this->getFixturePath('output'));

        $this->assertFileEquals($this->getFixturePath('appendSingle'), $this->getFixturePath('output'));
    }

    /**
     * @depends clone testLoad
     */
    public function testAppendMultiline($writer)
    {
        $writer
            ->add('
                First/Line/Path
                Second/Line/Path')
            ->save($this->getFixturePath('output'));

        $this->assertFileEquals($this->getFixturePath('appendMultiline'), $this->getFixturePath('output'));

    }

    /**
     * @depends clone testLoad
     */
    public function testAppendArray($writer)
    {
        $writer
            ->add([
                'array/1',
                'array/2',
                'array/3',
            ])->save($this->getFixturePath('output'));

        $this->assertFileEquals($this->getFixturePath('appendArray'), $this->getFixturePath('output'));
    }

    /**
     * @depends clone testLoad
     */
    public function testInsertBefore($writer)
    {
        $writer
            ->before(
                'working/',
                ['', 'insertedBefore', '']
            )->save($this->getFixturePath('output'));

        $this->assertFileEquals($this->getFixturePath('insertBefore'), $this->getFixturePath('output'));
    }

    /**
     * @depends clone testLoad
     */
    public function testInsertAfter($writer)
    {
        $writer
            ->after(
                'www/installcms.php',
                'www/install.php'
            )->save($this->getFixturePath('output'));

        $this->assertFileEquals($this->getFixturePath('insertAfter'), $this->getFixturePath('output'));
    }
}