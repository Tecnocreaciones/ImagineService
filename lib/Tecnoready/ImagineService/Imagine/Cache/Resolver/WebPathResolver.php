<?php

namespace Tecnoready\ImagineService\Imagine\Cache\Resolver;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Tecnoready\ImagineService\Binary\BinaryInterface;

class WebPathResolver implements ResolverInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $webRoot;
    /**
     * @var string
     */
    protected $cachePrefix;

    /**
     * @param Filesystem     $filesystem
     * @param RequestContext $request
     * @param string         $webRootDir
     * @param string         $cachePrefix
     */
    public function __construct(
        Filesystem $filesystem,
        Request $request,
        $webRootDir,
        $cachePrefix = 'media/cache'
    ) {
        $this->filesystem = $filesystem;
        $this->request = $request;

        $this->webRoot = rtrim(str_replace('//', '/', $webRootDir), '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->webRoot.'/'.$this->cachePrefix;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($path, $filter)
    {
        return sprintf('%s/%s',
            $this->getBaseUrl(),
            $this->getFileUrl($path, $filter)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isStored($path, $filter)
    {
        return is_file($this->getFilePath($path, $filter));
    }

    /**
     * {@inheritDoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->filesystem->dumpFile(
            $this->getFilePath($path, $filter),
            $binary->getContent()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array $paths, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            $filtersCacheDir = array();
            foreach ($filters as $filter) {
                $filtersCacheDir[] = $this->cacheRoot.'/'.$filter;
            }

            $this->filesystem->remove($filtersCacheDir);

            return;
        }

        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $this->filesystem->remove($this->getFilePath($path, $filter));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getFilePath($path, $filter)
    {
        return $this->webRoot.'/'.$this->getFileUrl($path, $filter);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFileUrl($path, $filter)
    {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);

        return $this->cachePrefix.'/'.$filter.'/'.ltrim($path, '/');
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        $port = '';
        if ('https' == $this->request->getScheme() && $this->request->getPort() != 443) {
            $port =  ":{$this->request->getPort()}";
        }

        if ('http' == $this->request->getScheme() && $this->request->getPort() != 80) {
            $port =  ":{$this->request->getPort()}";
        }

        $baseUrl = $this->request->getBaseUrl();
        if ('.php' == substr($this->request->getBaseUrl(), -4)) {
            $baseUrl = pathinfo($this->request->getBaseurl(), PATHINFO_DIRNAME);
        }
        $baseUrl = rtrim($baseUrl, '/\\');

        return sprintf('%s://%s%s%s',
            $this->request->getScheme(),
            $this->request->getHost(),
            $port,
            $baseUrl
        );
    }
}
