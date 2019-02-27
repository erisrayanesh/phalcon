<?php

namespace Phalcon\Http;

use Phalcon\Support\Interfaces\Arrayable;
use Phalcon\Support\Interfaces\Jsonable;

class FileResponse extends Response
{
	use ResponseTrait;

	public function __construct($file = null, $code = null, $headers = [], $attachmentName = null, $attachment = true)
	{
		parent::__construct(null, $code, null);
		$this->setFileToSend($file, $attachmentName, $attachment);

		if (!empty($headers)) {
			$this->withHeaders($headers);
		}
	}

	public function setContent($content)
	{
		if (null !== $content) {
			throw new \LogicException('The content cannot be set on a FileResponse instance.');
		}
	}

	public function appendContent($content)
	{
		if (null !== $content) {
			throw new \LogicException('Can not set append content on a FileResponse instance.');
		}
	}

	public function setJsonContent($content, $jsonOptions = 0, $depth = 512)
	{
		if (null !== $content) {
			throw new \LogicException('The json content cannot be set on a FileResponse instance.');
		}
	}
}