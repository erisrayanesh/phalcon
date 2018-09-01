<?php

namespace Phalcon\Mvc\Model\Traits;

use Phalcon\Mvc\Model\EagerLoading\Loader;

trait HasEagerLoading
{
    /**
     * <code>
     * <?php
     *
     * $limit  = 100;
     * $offset = max(0, $this->request->getQuery('page', 'int') - 1) * $limit;
     *
     * $manufacturers = Manufacturer::with('Robots.Parts', [
     *     'limit' => [$limit, $offset]
     * ]);
     *
     * foreach ($manufacturers as $manufacturer) {
     *     foreach ($manufacturer->robots as $robot) {
     *         foreach ($robot->parts as $part) { ... }
     *     }
     * }
     *
     * </code>
     *
     * @param mixed ...$arguments
     * @return \Phalcon\Mvc\ModelInterface[]
     */
    public static function with()
    {
        $arguments = func_get_args();

        $parameters = static::prepareParameters($arguments);

        $ret = static::find($parameters);

        if ($ret->count()) {
            array_unshift($arguments, $ret);

            $ret = call_user_func_array('Phalcon\Mvc\Model\EagerLoading\Loader::fromResultset', $arguments);
        }

        return $ret;
    }

    /**
     * Same as EagerLoadingTrait::with() for a single record
     *
     * @param mixed ...$arguments
     * @return false|\Phalcon\Mvc\ModelInterface
     */
    public static function findFirstWith()
    {
        $arguments = func_get_args();

        $parameters = static::prepareParameters($arguments);

        if ($ret = static::findFirst($parameters)) {
            array_unshift($arguments, $ret);

            $ret = call_user_func_array('Phalcon\Mvc\Model\EagerLoading\Loader::fromModel', $arguments);
        }

        return $ret;
    }

	public static function findFirstWithOrFail()
	{
		$retVal = static::findFirstWith(func_get_args());
		if (!empty($retVal)){
			return $retVal;
		}
		throw new ModelNotFoundException();
	}

    /**
     * <code>
     * <?php
     *
     * $manufacturer = Manufacturer::findFirstById(51);
     *
     * $manufacturer->load('Robots.Parts');
     *
     * foreach ($manufacturer->robots as $robot) {
     *    foreach ($robot->parts as $part) { ... }
     * }
     * </code>
     *
     * @param mixed ...$arguments
     * @return self
     */
    public function load()
    {
        $arguments = func_get_args();

        if (empty($arguments)){
        	$this;
		}

        array_unshift($arguments, $this);

        return call_user_func_array('Phalcon\Mvc\Model\EagerLoading\Loader::fromModel', $arguments);
    }

    private static function prepareParameters (&$arguments)
	{
		$parameters = null;

		if (!empty($arguments)) {
			$numArgs    = count($arguments);
			$lastArg    = $numArgs - 1;

//            if ($numArgs >= 2 && is_array($arguments[$lastArg])) {
			if ($numArgs >= 2) {
				$parameters = $arguments[$lastArg];

				unset($arguments[$lastArg]);

				if (is_callable($parameters)){
					$parameters = call_user_func($parameters);
				}

//                if (isset($parameters['columns'])) {
				if (is_array($parameters) && isset($parameters['columns'])) {
					throw new \LogicException('Results from database must be full models, do not use `columns` key');
				}
			}
		} else {
			throw new \BadMethodCallException(sprintf('%s requires at least one argument', __METHOD__));
		}

		return $parameters;
	}

}
