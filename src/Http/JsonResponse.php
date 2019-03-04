<?php

namespace Phalcon\Http;

use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Support\Interfaces\Jsonable;

class JsonResponse extends Response
{
	use ResponseTrait;

	//JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
	const DEFAULT_ENCODING_OPTIONS = 15;

	protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;

	public function __construct($content = null, $code = null, $headers = [], $encodingOptions = 15)
	{
		$this->encodingOptions = $encodingOptions;

		parent::__construct('', $code, null);

		if ($content === null) {
			$content = [];
		}

		if (!empty($headers)) {
			$this->withHeaders($headers);
		}

		$this->setJsonContent($content, $encodingOptions);
	}

	public function setContent($content)
	{
		return $this->setJsonContent($content, $this->encodingOptions);
	}

	public function setJsonContent($content, $jsonOptions = 0, $depth = 512)
	{
		$this->setContentType("application/json", "UTF-8");

		if ($content instanceof Jsonable) {
			$content = $content->toJson($jsonOptions ?? $this->encodingOptions, $depth);
		} elseif ($content instanceof \JsonSerializable) {
			$this->_content = $content->jsonSerialize();
		} elseif ($content instanceof Arrayable) {
			$content = $content->toArray();
		}

		if (array_accessible($content)){
			$content = json_encode($jsonOptions ?? $this->encodingOptions, $depth);
		}

		$this->_content = $content;
	}

	public function appendContent($content)
	{
		if (null !== $content) {
			throw new \LogicException('Can not set append content on a JsonResponse instance.');
		}
	}

	public function setFileToSend($filePath, $attachmentName = null, $attachment = true)
	{
		if (null !== $filePath) {
			throw new \LogicException('Can not send a file on a JsonResponse instance.');
		}
	}

	public function setFile($file)
	{
		if (null !== $file) {
			throw new \LogicException('Can not set file on a JsonResponse instance.');
		}
	}
}