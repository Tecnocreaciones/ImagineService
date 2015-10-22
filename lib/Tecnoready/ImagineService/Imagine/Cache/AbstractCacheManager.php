<?php

namespace Tecnoready\ImagineService\Imagine\Cache;

use Tecnoready\ImagineService\Binary\BinaryInterface;
use Tecnoready\ImagineService\Imagine\Cache\Resolver\ResolverInterface;
use Tecnoready\ImagineService\Imagine\Filter\FilterConfiguration;

abstract class AbstractCacheManager implements CacheManagerInterface
{
    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var ResolverInterface[]
     */
    protected $resolvers = array();

    /**
     * @var SignerInterface
     */
    protected $signer;

    /**
     * @var string
     */
    protected $defaultResolver;

    /**
     * Constructs the cache manager to handle Resolvers based on the provided FilterConfiguration.
     *
     * @param FilterConfiguration      $filterConfig
     * @param SignerInterface          $signer
     * @param string                   $defaultResolver
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        SignerInterface $signer,
        $defaultResolver = null
    ) {
        $this->filterConfig = $filterConfig;
        $this->signer = $signer;
        $this->defaultResolver = $defaultResolver ?: 'default';
    }

    /**
     * Adds a resolver to handle cached images for the given filter.
     *
     * @param string            $filter
     * @param ResolverInterface $resolver
     */
    public function addResolver($filter, ResolverInterface $resolver)
    {
        $this->resolvers[$filter] = $resolver;

        if ($resolver instanceof CacheManagerAwareInterface) {
            $resolver->setCacheManager($this);
        }
    }

    /**
     * Gets a resolver for the given filter.
     *
     * In case there is no specific resolver, but a default resolver has been configured, the default will be returned.
     *
     * @param string $filter
     *
     * @return ResolverInterface
     *
     * @throws \OutOfBoundsException If neither a specific nor a default resolver is available.
     */
    protected function getResolver($filter)
    {
        $config = $this->filterConfig->get($filter);

        $resolverName = empty($config['cache']) ? $this->defaultResolver : $config['cache'];

        if (!isset($this->resolvers[$resolverName])) {
            throw new \OutOfBoundsException(sprintf(
                'Could not find resolver "%s" for "%s" filter type',
                $resolverName,
                $filter
            ));
        }

        return $this->resolvers[$resolverName];
    }

    /**
     * Gets filtered path for rendering in the browser.
     * It could be the cached one or an url of filter action.
     *
     * @param string $path          The path where the resolved file is expected.
     * @param string $filter
     * @param array  $runtimeConfig
     *
     * @return string
     */
    public function getBrowserPath($path, $filter, array $runtimeConfig = array())
    {
        if (!empty($runtimeConfig)) {
            $rcPath = $this->getRuntimePath($path, $runtimeConfig);

            return $this->isStored($rcPath, $filter) ?
                $this->resolve($rcPath, $filter) :
                $this->generateUrl($path, $filter, $runtimeConfig)
            ;
        }

        return $this->isStored($path, $filter) ?
            $this->resolve($path, $filter) :
            $this->generateUrl($path, $filter)
        ;
    }

    /**
     * Get path to runtime config image.
     *
     * @param string $path
     * @param array  $runtimeConfig
     */
    public function getRuntimePath($path, array $runtimeConfig)
    {
        return 'rc/'.$this->signer->sign($path, $runtimeConfig).'/'.$path;
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path          The path where the resolved file is expected.
     * @param string $filter        The name of the imagine filter in effect.
     * @param array  $runtimeConfig
     *
     * @return string
     */
//    public function generateUrl($path, $filter, array $runtimeConfig = array())
//    {
//        $params = array(
//            'path' => ltrim($path, '/'),
//            'filter' => $filter,
//        );
//
//        if (empty($runtimeConfig)) {
//            $filterUrl = $this->router->generate('liip_imagine_filter', $params, true);
//        } else {
//            $params['filters'] = $runtimeConfig;
//            $params['hash'] = $this->signer->sign($path, $runtimeConfig);
//
//            $filterUrl = $this->router->generate('liip_imagine_filter_runtime', $params, true);
//        }
//
//        return $filterUrl;
//    }

    /**
     * Checks whether the path is already stored within the respective Resolver.
     *
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function isStored($path, $filter)
    {
        return $this->getResolver($filter)->isStored($path, $filter);
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path
     * @param string $filter
     *
     * @return string The url of resolved image.
     *
     * @throws NotFoundHttpException if the path can not be resolved
     */
    public function resolve($path, $filter)
    {
        if (false !== strpos($path, '/../') || 0 === strpos($path, '../')) {
            throw $this->createNotFoundHttpException(sprintf("Source image was searched with '%s' outside of the defined root path", $path));
        }

        $url = $this->getResolver($filter)->resolve($path,$filter);

        return $url;
    }

    /**
     * @see ResolverInterface::store
     *
     * @param BinaryInterface $binary
     * @param string          $path
     * @param string          $filter
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->getResolver($filter)->store($binary, $path, $filter);
    }

    /**
     * @param string|string[]|null $paths
     * @param string|string[]|null $filters
     */
    public function remove($paths = null, $filters = null)
    {
        if (null === $filters) {
            $filters = array_keys($this->filterConfig->all());
        }
        if (!is_array($filters)) {
            $filters = array($filters);
        }
        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $paths = array_filter($paths);
        $filters = array_filter($filters);

        $mapping = new \SplObjectStorage();
        foreach ($filters as $filter) {
            $resolver = $this->getResolver($filter);

            $list = isset($mapping[$resolver]) ? $mapping[$resolver] : array();

            $list[] = $filter;

            $mapping[$resolver] = $list;
        }

        foreach ($mapping as $resolver) {
            $resolver->remove($paths, $mapping[$resolver]);
        }
    }
    protected function createNotFoundHttpException($message = null, \Exception $previous = null, $code = 0) {
        return new \Tecnoready\ImagineService\Exception\NotFoundHttpException($message, $previous,$code);
    }
}
