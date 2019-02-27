<?php

namespace Phalcon\Http;

use ArrayObject;
use JsonSerializable;
use Phalcon\Support\Interfaces\Jsonable;
use Phalcon\Support\Interfaces\Arrayable;
//use Renderable;

class HttpResponse extends Response
{

	use ResponseTrait;

	public function __construct($content = null, $code = null, $status = null, $headers = null)
	{
		parent::__construct($content, $code, $status);

		if (!empty($headers)){
			$this->withHeaders($headers);
		}
	}

	/**
     * Set the content on the response.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function setContent($content)
    {
        if ($this->shouldBeJson($content)) {
            $this->header('Content-Type', 'application/json');
            $content = $this->convertToJson($content);
        }

        parent::setContent($content);

        return $this;
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof Arrayable ||
               $content instanceof Jsonable ||
               $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }

    /**
     * Converts the given content into JSON.
     *
     * @param  mixed   $content
     * @return string
     */
    protected function convertToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        } elseif ($content instanceof Arrayable) {
            return json_encode($content->toArray());
        }

        return json_encode($content);
    }
}
