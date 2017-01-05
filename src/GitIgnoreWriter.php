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
        $this->buffer = array_map(function($line) {
           return rtrim($line, "\r\n") . PHP_EOL;
        });

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
     */
    public function toArray()
    {
        return $this->buffer;
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
     * Find the existing value of the given environment variable. Returns false
     * if the variable doesn't exist, or an array containing the full line as
     * well as its components broken out.
     *
     * @param type $key
     * @return boolean|array
     */
    public function get($key)
    {
        // first, find the quote style
        $pattern = '/^(export\h)?\h*'.preg_quote($key, '/').'\h*=\h*(?P<quote>[\'"])?/m';
        if (!preg_match($pattern, $this->buffer, $m)) {
            return false;
        }

        if (!empty($m['quote'])) {
            // if it has quotes then allow for escaped quotes, whitespace, etc.
            $quote = $m['quote'];
            $pattern = '/^(?P<export>export\h)?\h*(?P<key>'.preg_quote($key, '/').')\h*=\h*'.$quote.'(?P<value>[^'.$quote.'\\\\]*(?:\\\\.[^'.$quote.'\\\\]*)*)'.$quote.'\h*(?:#\h*(?P<comment>.*))?$/m';
            if (!preg_match($pattern, $this->buffer, $m)) {
                return false;
            }
            $m['value'] = str_replace('\\\\', '\\', $m['value']);
            $m['value'] = str_replace("\\$quote", $quote, $m['value']);
        } else {
            // if it's not quoted then it should just be one string of basic word characters
            $pattern = '/^(?P<export>export\h)?\h*(?P<key>'.preg_quote($key, '/').')\h*=\h*(?P<value>.*?)\h*(?:#\h*(?P<comment>.*))?$/m';
            if (!preg_match($pattern, $this->buffer, $m)) {
                return false;
            }
        }

        return [
            'line' => $m[0],
            'export' => (strlen($m['export']) > 0),
            'key' => $m['key'],
            'value' => $m['value'],
            'comment' => isset($m['comment']) ? $m['comment'] : ''
        ];
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