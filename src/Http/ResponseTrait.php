<?php

namespace Phalcon\Http;

use Phalcon\Http\Response\HeadersInterface;

trait ResponseTrait
{
    /**
     * The original content of the response.
     *
     * @var mixed
     */
    public $original;

    /**
     * Get the status code for the response.
     *
     * @return int
     */
    public function status()
    {
        return $this->getStatusCode();
    }

    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * Get the original response content.
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        $original = $this->original;

        return $original instanceof self ? $original->{__FUNCTION__}() : $original;
    }

    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  array|string  $values
     * @param  bool    $replace
     * @return $this
     */
    public function header($key, $values)
    {
        $this->setHeader($key, $values);

        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param  HeadersInterface|array  $headers
     * @return $this
     */
    public function withHeaders($headers)
    {
        if ($headers instanceof HeadersInterface) {
	        return $this->setHeaders($headers);
        }

        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param  Cookie|mixed  $cookie
     * @return $this
     */
    public function cookie($cookie)
    {
        return call_user_func_array([$this, 'withCookie'], func_get_args());
    }

    /**
     * Add a cookie to the response.
     *
     * @param  Cookie|mixed  $cookie
     * @return $this
     */
    public function withCookie($cookie)
    {
        if (is_string($cookie) && function_exists('cookie')) {
            $cookie = call_user_func_array('cookie', func_get_args());
        }

	    call_user_func_array([$this->_cookies, 'set'], $cookie);

        return $this;
    }

}