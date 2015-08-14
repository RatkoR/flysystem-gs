<?php namespace RatkoR\Flysystem\Gs;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Config;
use League\Flysystem\Util;

class GsAdapter extends Local
{
    protected static $permissions = [
        'public' => 'public-read',
        'private' => 'private',
        'dir'     => ['public'=>'0777'],
    ];

   /**
     * Constructor.
     *
     * @param string $root
     */
    public function __construct($root)
    {
        $this->setPathPrefix($root);
    }

    protected function ensureDirectory($root)
    {
        return $root;
    }

    public function write($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));

        $options = [
            'gs' => [
                'acl' => static::$permissions['public'],
                'Content-Type' => Util::guessMimeType($path, $contents),
            ]
        ];
        $ctx = stream_context_create($options);

        if (($size = file_put_contents($location, $contents, 0, $ctx)) === false) {
            return false;
        }

        $type = 'file';
        $result = compact('contents', 'type', 'size', 'path');

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

    public function setVisibility($path, $visibility)
    {
        $location = $this->applyPathPrefix($path);

        $options = ['gs' => ['acl' => static::$permissions[$visibility]]];
        $ctx = stream_context_create($options);

        rename($location, $location.'1', $ctx);
        rename($location.'1', $location, $ctx);

        return compact('visibility');
    }
}
