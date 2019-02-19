<?php

namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Adapter;
use Phalcon\Logger\FormatterInterface;
use Phalcon\Logger\Formatter\Line as LineFormatter;

/**
 * RotatingFile Adapter
 * @package Phalcon\Logger\Adapter
 */
class RotatingFile extends Adapter
{
	public const FILE_PER_DAY = 'daily';
	public const FILE_PER_WEEK = 'weekly';
	public const FILE_PER_MONTH = 'monthly';
	public const FILE_PER_YEAR = 'yearly';

	protected $path;
	protected $url;
	protected $fileHandler;
	protected $mustRotate = false;
	protected $dateFormats;
	protected $options;

	/**
	 * RotatingFile constructor.
	 * @param string $name
	 * @param array|null $options
	 * @throws \Exception
	 */
	public function __construct(string $name, $options = null)
	{
		if (substr_count($name, '{date}') === 0) {
			throw new \InvalidArgumentException(
				'Invalid filename format - filename must contain at least `{date}`, because otherwise rotating is impossible.'
			);
		}
		$this->path = $name;
		$this->options = $options;
		$this->dateFormats = [
			self::FILE_PER_DAY => "Y-m-d",
			self::FILE_PER_WEEK => "Y-m_W",
			self::FILE_PER_MONTH => "Y-m",
			self::FILE_PER_YEAR => "Y",
		];
		$this->url = $this->getTimedFilename();
	}

	/**
	 * Closes the file handler
	 * @return bool
	 */
	public function close(): bool
	{
		if ($this->mustRotate === true) {
			$this->rotate();
		}

		if (is_resource($this->fileHandler)){
			return fclose($this->fileHandler);
		}

		return true;
	}

	/**
	 * Returns the internal formatter
	 * @return FormatterInterface
	 */
	public function getFormatter(): FormatterInterface
	{
		if (! is_object($this->_formatter)) {
			$this->_formatter = new LineFormatter();
		}

		return $this->_formatter;
	}

	/**
	 * Writes the log to the file itself
	 * @param string $message
	 * @param int $type
	 * @param int $time
	 * @param array|null $context
	 * @throws \Exception
	 */
	public function logInternal(string $message, $type, $time, array $context = null): void
	{
		if (is_null($this->mustRotate)) {
			$this->mustRotate = !file_exists($this->url);
		}

		if ($this->needsRotate($time)) {
			$this->mustRotate = true;
			$this->close();
		}

		if (! is_resource($this->fileHandler)) {
			$this->openFileHandler();
		}

		fwrite($this->fileHandler, $this->getFormatter()->format($message, $type, $time, $context));
	}

	/**
	 * Returns log files template path
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Returns formatted file path
	 * @return string
	 * @throws \Exception
	 */
	protected function getTimedFilename(): string
	{
		$fileInfo = pathinfo($this->getPath());
		$timedFilename = str_replace(
			'{date}',
			$this->getPeriodStart()->format($this->getPeriodDateFormat()),
			$fileInfo['filename']
		);

		if (!empty($fileInfo['extension'])) {
			$timedFilename .= '.'.$fileInfo['extension'];
		}

		return $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $timedFilename;
	}

	/**
	 * Returns assigned period
	 * @return string
	 */
	protected function getPeriod(): string
	{
		return $this->options['period'] ?: static::FILE_PER_DAY;
	}

	/**
	 * Returns assigned date format of current period
	 * @return string
	 */
	protected function getPeriodDateFormat(): string
	{
		return $this->dateFormats[$this->getPeriod()] ?: $this->dateFormats[static::FILE_PER_DAY] ;
	}

	/**
	 * Returns minimum DateTime of current period
	 * @return \DateTimeImmutable
	 * @throws \Exception
	 */
	protected function getPeriodStart(): \DateTimeImmutable
	{
		switch ($this->getPeriod()){
			case static::FILE_PER_WEEK:
				return new \DateTimeImmutable('this week midnight');
			case static::FILE_PER_MONTH:
				return new \DateTimeImmutable('first day of this month midnight');
			case static::FILE_PER_YEAR:
				return new \DateTimeImmutable('1st january this year midnight');
			case static::FILE_PER_DAY:
			default:
				return new \DateTimeImmutable('today');
		}
	}

	/**
	 * Returns maximum DateTime of current period
	 * @return \DateTimeImmutable
	 * @throws \Exception
	 */
	protected function getPeriodFinish(): \DateTimeImmutable
	{
		switch ($this->getPeriod()){
			case static::FILE_PER_WEEK:
				return new \DateTimeImmutable('next week midnight');
			case static::FILE_PER_MONTH:
				return new \DateTimeImmutable('first day of next month midnight');
			case static::FILE_PER_YEAR:
				return new \DateTimeImmutable('1st january next year midnight');
			case static::FILE_PER_DAY:
			default:
				return new \DateTimeImmutable('tomorrow');
		}
	}

	/**
	 * Checks if the given time is in next period and requires a period rotation
	 * @param $time
	 * @return bool
	 * @throws \Exception
	 */
	protected function needsRotate($time): bool
	{
		return $this->getPeriodFinish()->getTimestamp() <= $time;
	}

	/**
	 * Creates a file resource handler
	 * @throws \Exception
	 */
	protected function openFileHandler(): void
	{
		if (!is_array($this->options)) {
			throw new \Exception("Can't open log file at '" . $this->getTimedFilename() . "'");
		}

		$mode = null;

		if (isset($this->options["mode"])) {
			$mode = $this->options["mode"];
			if (strpos($mode, "r") === false) {
				throw new \Exception("Logger must be opened in append or write mode");
			}
		}

		if (empty($mode)) {
			$mode = "ab";
		}

		if (! is_resource($handler = fopen($this->url, $mode))) {
			throw new \Exception("Can't open log file at '" . $this->url . "'");
		}

		$this->fileHandler = $handler;
	}

	/**
	 * Rotates the files.
	 */
	protected function rotate(): void
	{
		$this->url = $this->getTimedFilename();
		$this->mustRotate = false;
	}
}