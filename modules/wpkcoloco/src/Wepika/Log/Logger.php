<?php

namespace WpkColoco\Wepika\Log;

use Exception;
use InvalidArgumentException;

/**
 * Class Logger
 *
 * @package WpkColoco\Wepika\Log
 */
class Logger
{
    /**
     * @var
     */
    private $logs_directory;

    /**
     * Logger constructor.
     *
     * $logs_directory must ends with a '/'
     *
     * @param string $logs_directory
     */
    public function __construct($logs_directory)
    {
        if (!file_exists($logs_directory) && !is_dir($logs_directory) && !is_writable($logs_directory)) {
            throw new InvalidArgumentException("logs_directory must be an existing and writable directory");
        }

        $this->logs_directory = $logs_directory;
    }

    /**
     * @param string|Exception $message
     */
    public function log($message)
    {
        file_put_contents(
            $this->getFilePath(),
            $this->formatMessage($message),
            FILE_APPEND
        );
    }

    /**
     * @return string
     */
    private function getFilePath()
    {
        return $this->logs_directory . $this->getFileName();
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return date('Y-m-d') . '_log.log';
    }

    /**
     * @param string|Exception $message
     * @return string
     */
    private function formatMessage($message)
    {
        if ($message instanceof Exception) {
            $message = '[in ' . $message->getFile() . ' line ' . $message->getLine() . '] ' . $message->getMessage();
        }

        return '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    }
}
