<?php

namespace Phalcon\Http;

interface KernelInterface
{
    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap();

    /**
     * Handle an incoming HTTP request.
     *
     * @param  Request  $request
     * @return Response
     */
    public function handle(RequestInterface $request);

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function terminate(RequestInterface $request, ResponseInterface $response);

    /**
     * Returns application instance.
     *
     * @return \Phalcon\Bootstrap\Application
     */
    public function getApplication();
}
