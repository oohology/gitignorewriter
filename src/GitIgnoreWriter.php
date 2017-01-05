<?php

namespace GitIgnoreWriter;

class GitIgnoreWriter
{
    /**
     * The full contents of the file during processing
     * @var array
     */
    protected $buffer = [];

    /**
     * Path where the output file will be written
     *
     * @var string
     */
    protected $outputPath;

    /**
     * Line number to write next line
     */
    protected $pointer = 0;

    /**
     * Create the instance. If a $filePath is given it must be writable, although
     * output can be later redirected to a different path.
     *
     * @param string $filePath
     * @param bool
     */
    public function __construct($filePath = null)
    {
        if (!is_null($filePath)) {
            if (is_file($filePath)) {
                $this->load($filePath);
            }
            $this->setOutputPath($filePath);
        }
    }

    /**
     * Read the contents of a file into the buffer
     *
     * @param string $filePath
     * @return \GitIgnoreWriter\GitIgnoreWriter
     * @throws \Exception
     */
    public function load($filePath)
    {
        if (!is_file($filePath) || (!false === ($buffer = file($filePath)))) {
            throw new \Exception(sprintf('Unable to read file at %s.', $filePath));
        }
        $this->buffer = array_map('trim', $buffer);

        $this->pointer = count($this->buffer);

        return $this;
    }

    /**
     * Set the path to write the output (if different from the source file)
     *
     * @param string $filePath
     * @param bool $create
     * @return \GitIgnoreWriter\GitIgnoreWriter
     */
    public function setOutputPath($filePath)
    {
        if (false === $this->ensureFileIsWritable($filePath)) {
            throw new \Exception(sprintf('Unwritable file at %s.', $filePath));
        }

        $this->outputPath = $filePath;

        return $this;
    }

    /**
     * Returns the current buffer as a raw array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->buffer;
    }

    /**
     * Add lines to the file at the current pointer
     *
     * @param array|string $input
     * @return \GitIgnoreWriter\GitIgnoreWriter
     */
    public function add($input)
    {
        $inputLines = $this->parseInput($input);

        foreach($inputLines as $k => $line) {
            if(
                strlen($line)
                && (strpos($line, '#') !== 0)
                && $this->exists($line)
            ) {
                unset($inputLines[$k]);
            }
        }

        $after = array_splice($this->buffer, $this->pointer);
        foreach($inputLines as $line) {
            $this->buffer[] = $line;
            ++$this->pointer;
        }
        $this->buffer = array_merge($this->buffer, $after);

        return $this;
    }

    /**
     * Inserts lines into the file before the given value
     *
     * @param string $find
     * @param array|string $input
     * @return \GitIgnoreWriter\GitIgnoreWriter
     */
    public function before($find, $input)
    {
        if(false !== ($pointer = array_search(trim($find), $this->buffer, true))) {
            $this->pointer = $pointer;
        }

        return $this->add($input);
    }

    /**
     * Inserts lines into the file after the given value
     *
     * @param string $find
     * @param array|string $input
     * @return \GitIgnoreWriter\GitIgnoreWriter
     */
    public function after($find, $input)
    {
        if(false !== ($pointer = array_search(trim($find), $this->buffer, true))) {
            $this->pointer = ++$pointer;
        }

        return $this->add($input);
    }

    /**
     * Parse an input value into an array of lines
     */
    protected function parseInput($input)
    {
        if(is_array($input)) {
            $result = [];
            foreach($input as $value) {
                $result = array_merge($result, $this->parseInput($value));
            }
            return $result;
        }

        return array_values(array_map('trim', preg_split('/[\r\n]/', $input)));
    }


    /**
     * Write the changes to the file
     *
     * @return \GitIgnoreWriter\GitIgnoreWriter
     * @throws \Exception
     */
    public function save($filePath = null)
    {
        if (!is_null($filePath)) {
            $this->setOutputPath($filePath);
        }

        if (is_null($this->outputPath)) {
            throw new \Exception('Output file path is not set');
        }

        if (false === file_put_contents($this->outputPath, implode(PHP_EOL, $this->buffer)."\n")) {
            throw new \Exception(sprintf('Failed to write file at %s.', $this->outputPath));
        }

        return $this;
    }

    /**
     * Test if the given value exists in the file
     *
     * @param string $value
     * @return boolean
     */
    public function exists($value)
    {
        return in_array($value, $this->buffer, true);
    }

    /**
     * Tests the file for writability. If the file doesn't exist, check
     * the parent directory for writability so the file can be created.
     *
     * @return bool
     */
    protected function ensureFileIsWritable($filePath)
    {
        if ((is_file($filePath) && !is_writable($filePath)) || (!is_file($filePath) && !is_writable(dirname($filePath)))) {
            return false;
        }
        return true;
    }
}