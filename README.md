# ImagineService
Manage and optimize images with cache and filters using Imagine

# My filter and config definition
<pre>
$filterConfig = new Tecnoready\ImagineService\Imagine\Filter\FilterConfiguration();
    
    $quality = 90;
    $pngCompressionLevel = 9;
    $filterConfig->set("thumb", [
        "filters" => [
            "thumbnail" => [
                "size" => array(200, 200),
            ],
        ],
        'quality' => $quality,
        'jpeg_quality' => $quality,
        'png_compression_level' => $pngCompressionLevel,
        
    ]);
    $filterConfig->set("medium", [
        "filters" => [
            "relative_resize" => [
                "heighten" => 400,
            ],
        ],
        'quality' => $quality,
        'jpeg_quality' => $quality,
        'png_compression_level' => $pngCompressionLevel,
    ]);
    $filterConfig->set("large", [
        "filters" => [
            "relative_resize" => [
                "heighten" => 800,
            ],
        ],
        'quality' => $quality,
        'jpeg_quality' => $quality,
        'png_compression_level' => $pngCompressionLevel,
    ]);
    
    $secret = "tokenToSingFiles";
    # Drive
    $drive = Tecnoready\ImagineService\Imagine\ImagineServiceBuilder::DRIVE_GD;
    
    $imagineServiceBuilder = new \Tecnoready\ImagineService\Imagine\ImagineServiceBuilder([
        "web_root_dir" => "./mi/app/public/",
        "cache_prefix" => 'media/cache',
    ]);
    # Assistant to build the service
    $imagineServiceBuilder
            ->withDrive($drive)
            ->withCacheManagerClass("\common\services\imagine\MyCacheManager")
            ->withFilterConfig($filterConfig)
            ->withSecret($secret)
            ;
    $imagineService = $imagineServiceBuilder->build();
</pre>

# Controller o resolve path example in index o path:
<pre>

      $filter = $request->get("filter");
        $path = $request->get("path");
        
        $response = new Response();
       
        /**
         * \Tecnoready\ImagineService\Imagine\ImagineService
         * $imagineService 
         **/
        

        $cacheManager = $imagineService->getCacheManager();
        $dataManager = $imagineService->getDataManager();
        $filterManager = $imagineService->getFilterManager();
        try {
            if (!$cacheManager->isStored($path, $filter)) {
                try {
                    $binary = $dataManager->find($filter, $path);
                } catch (\Tecnoready\ImagineService\Exception\Binary\Loader\NotLoadableException $e) {
                    if ($defaultImageUrl = $dataManager->getDefaultImageUrl($filter)) {
                        return $response->redirect($defaultImageUrl,302);
                    }

                    throw new \yii\web\NotFoundHttpException('Source image could not be found', 0,$e);
                }
                $cacheManager->store(
                    $filterManager->applyFilter($binary, $filter),
                    $path,
                    $filter
                );
            }

            return $response->redirect($cacheManager->resolve($path, $filter), 301);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }

</pre>
