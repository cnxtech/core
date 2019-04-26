<?php /** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnhandledExceptionInspection */


namespace Codex\Filesystem;


use BadMethodCallException;
use League\Flysystem\FilesystemInterface;

class File
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var FilesystemInterface|\Codex\Filesystem\Local
     */
    protected $filesystem;

    /** @var \Codex\Filesystem\File */
    protected $srcFile;

    /**
     * Constructor.
     *
     * @param FilesystemInterface         $filesystem
     * @param string                      $path
     * @param \Codex\Filesystem\File|null $srcFile
     */
    public function __construct(FilesystemInterface $filesystem = null, $path = null, File $srcFile = null)
    {
        $this->path       = $path;
        $this->filesystem = $filesystem;
        $this->srcFile    = $srcFile;
    }

    public function getSrcFile()
    {
        return $this->srcFile;
    }

    public function setSrcFile(File $srcFile)
    {
        $this->srcFile = $srcFile;
        return $this;
    }



    /**
     * Check whether the file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->filesystem->has($this->path);
    }

    /**
     * Read the file.
     *
     * @return string|false file contents
     */
    public function read()
    {
        return $this->filesystem->read($this->path);
    }

    /**
     * Read the file as a stream.
     *
     * @return resource|false file stream
     */
    public function readStream()
    {
        return $this->filesystem->readStream($this->path);
    }

    /**
     * Write the new file.
     *
     * @param string $content
     *
     * @return bool success boolean
     */
    public function write($content)
    {
        return $this->filesystem->write($this->path, $content);
    }

    /**
     * Write the new file using a stream.
     *
     * @param resource $resource
     *
     * @return bool success boolean
     */
    public function writeStream($resource)
    {
        return $this->filesystem->writeStream($this->path, $resource);
    }

    /**
     * Update the file contents.
     *
     * @param string $content
     *
     * @return bool success boolean
     */
    public function update($content)
    {
        return $this->filesystem->update($this->path, $content);
    }

    /**
     * Update the file contents with a stream.
     *
     * @param resource $resource
     *
     * @return bool success boolean
     */
    public function updateStream($resource)
    {
        return $this->filesystem->updateStream($this->path, $resource);
    }

    /**
     * Create the file or update if exists.
     *
     * @param string $content
     *
     * @return bool success boolean
     */
    public function put($content)
    {
        return $this->filesystem->put($this->path, $content);
    }

    /**
     * Create the file or update if exists using a stream.
     *
     * @param resource $resource
     *
     * @return bool success boolean
     */
    public function putStream($resource)
    {
        return $this->filesystem->putStream($this->path, $resource);
    }

    /**
     * Rename the file.
     *
     * @param string $newpath
     *
     * @return bool success boolean
     */
    public function rename($newpath)
    {
        if ($this->filesystem->rename($this->path, $newpath)) {
            $this->path = $newpath;

            return true;
        }

        return false;
    }

    /**
     * Copy the file.
     *
     * @param string $newpath
     *
     * @return \League\Flysystem\File|false new file or false
     */
    public function copy($newpath)
    {
        if ($this->filesystem->copy($this->path, $newpath)) {
            return new File($this->filesystem, $newpath);
        }

        return false;
    }

    /**
     * Get the file's timestamp.
     *
     * @return string|false The timestamp or false on failure.
     */
    public function getTimestamp()
    {
        return $this->filesystem->getTimestamp($this->path);
    }

    /**
     * Get the file's mimetype.
     *
     * @return string|false The file mime-type or false on failure.
     */
    public function getMimetype()
    {
        return $this->filesystem->getMimetype($this->path);
    }

    /**
     * Get the file's visibility.
     *
     * @return string|false The visibility (public|private) or false on failure.
     */
    public function getVisibility()
    {
        return $this->filesystem->getVisibility($this->path);
    }

    /**
     * Get the file's metadata.
     *
     * @return array|false The file metadata or false on failure.
     */
    public function getMetadata()
    {
        return $this->filesystem->getMetadata($this->path);
    }

    /**
     * Get the file size.
     *
     * @return int|false The file size or false on failure.
     */
    public function getSize()
    {
        return $this->filesystem->getSize($this->path);
    }

    /**
     * Delete the file.
     *
     * @return bool success boolean
     */
    public function delete()
    {
        return $this->isDir() ? $this->filesystem->deleteDir($this->path) : $this->filesystem->delete($this->path);
    }


    /**
     * Check whether the entree is a directory.
     *
     * @return bool
     */
    public function isDir()
    {
        return $this->getType() === 'dir';
    }

    /**
     * Check whether the entree is a file.
     *
     * @return bool
     */
    public function isFile()
    {
        return $this->getType() === 'file';
    }

    /**
     * Retrieve the entree type (file|dir).
     *
     * @return string file or dir
     */
    public function getType()
    {
        $metadata = $this->filesystem->getMetadata($this->path);

        return $metadata[ 'type' ];
    }

    /**
     * Set the Filesystem object.
     *
     * @param FilesystemInterface $filesystem
     *
     * @return $this
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * @return \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set the entree path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Retrieve the entree path.
     *
     * @return string path
     */
    public function getPath()
    {
        return $this->path;
    }

    public function key()
    {
        return $this->getAbsolutePath();
    }

    public function getAbsolutePath()
    {
        return path_absolute($this->path, '/');
    }

    public function getFilename($removeExtension = false)
    {
        return $removeExtension ? path_get_filename_without_extension($this->path) : path_get_filename($this->path);
    }

    public function getDirectory()
    {
        return path_get_directory($this->path);
    }

    public function getDirectoryName()
    {
        return path_get_directory_name($this->path);
    }

    public function getExtension()
    {
        return path_get_extension($this->path);
    }


    /**
     * Plugins pass-through.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        array_unshift($arguments, $this->path);
        $callback = [ $this->filesystem, $method ];

        try {
            return call_user_func_array($callback, $arguments);
        }
        catch (BadMethodCallException $e) {
            throw new BadMethodCallException(
                'Call to undefined method '
                . get_called_class()
                . '::' . $method
            );
        }
    }

    /**
     * @return \League\Flysystem\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->filesystem->getAdapter();
    }
}
