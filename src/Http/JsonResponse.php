<?php


namespace Phalcon\Http;


use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Support\Interfaces\Jsonable;

class JsonResponse extends Response
{
	//JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
	const DEFAULT_ENCODING_OPTIONS = 15;

	protected $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;

	public function __construct($content = null, $code = null, $status = null, $encodingOptions = 15)
	{
		$this->encodingOptions = $encodingOptions;

		parent::__construct('', $code, $status);

		if ($content === null) {
			$content = [];
		}

		$this->setJsonContent($content, $encodingOptions);
	}

	public function setContent($content)
	{
		if ($content instanceof Jsonable) {
			$this->_content = $content->toJson($this->encodingOptions);
		} elseif ($content instanceof \JsonSerializable) {
			$this->_content = json_encode($content->jsonSerialize(), $this->encodingOptions);
		} elseif ($content instanceof Arrayable) {
			$this->_content = json_encode($content->toArray(), $this->encodingOptions);
		} else {
			$this->_content = json_encode($content, $this->encodingOptions);
		}

		return $this->setJsonContent($content, $this->encodingOptions);
	}
}