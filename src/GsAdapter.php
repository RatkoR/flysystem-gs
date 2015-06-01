<?php namespace RatkoR\Flysystem\Gs;

use League\Flysystem\Adapter\Local;

class GsAdapter extends Local
{
   /**
     * Constructor.
     *
     * @param string $root
     */
    public function __construct($root)
    {
        $realRoot = $this->ensureDirectory($root);

        $this->setPathPrefix($realRoot);
    }

    public function write($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));

        if (($size = file_put_contents($location, $contents)) === false) {
            return false;
        }

        $type = 'file';
        $result = compact('contents', 'type', 'size', 'path');

        if ($visibility = $config->get('visibility')) {
            $result['visibility'] = $visibility;
            $this->setVisibility($path, $visibility);
        }

        return $result;
    }

    public function update($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $mimetype = Util::guessMimeType($path, $contents);

        if (($size = file_put_contents($location, $contents)) === false) {
            return false;
        }

        return compact('path', 'size', 'contents', 'mimetype');
    }
}