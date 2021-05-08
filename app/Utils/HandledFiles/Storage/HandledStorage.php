<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Utils\HandledFiles\Storage;

use App\Exceptions\AppException;
use App\Utils\HandledFiles\File;
use App\Utils\HandledFiles\Helper;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Illuminate\Support\Str;

abstract class HandledStorage extends Storage implements IFileStorage, IResponseStorage, IEncryptionStorage
{
    use EncryptionStorageTrait;

    /**
     * @var FilesystemAdapter
     */
    protected $disk;

    protected $config;

    protected $relativePath;

    /**
     * HandledStorage constructor.
     * @param FilesystemAdapter|null $disk
     * @throws
     */
    public function __construct($disk = null)
    {
        if (!empty(static::NAME)) {
            $this->config = config(sprintf('filesystems.disks.%s', static::NAME));
        }

        $this->setDisk($disk);
    }

    /**
     * @param FilesystemAdapter|null $disk
     * @return static
     * @throws
     */
    public function setDisk($disk = null)
    {
        if (empty($disk)) {
            $disk = StorageFacade::disk(static::NAME);
        }
        if (is_string($disk)) {
            $disk = StorageFacade::disk($disk);
        }
        if (!($disk instanceof Filesystem)) {
            throw new AppException('Disk was not allowed');
        }
        $this->disk = $disk;

        return $this;
    }

    public function getDiskName()
    {
        return $this->getName();
    }

    /**
     * @param string $relativePath
     * @return static
     */
    public function setRelativePath($relativePath)
    {
        $this->relativePath = $relativePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return string
     */
    public function getRelativeDirectory()
    {
        return dirname($this->relativePath);
    }

    public function getFilename()
    {
        return pathinfo($this->relativePath, PATHINFO_FILENAME);
    }

    public function getBasename()
    {
        return basename($this->relativePath);
    }

    public function getExtension()
    {
        return pathinfo($this->relativePath, PATHINFO_EXTENSION);
    }

    /**
     * @param HandledStorage|UploadedFile|File|string $file
     * @param string $toDirectory
     * @param bool|string|array $keepOriginalName
     * @param string $visibility
     * @return static
     */
    public function from($file, $toDirectory = '', $keepOriginalName = true, $visibility = 'public')
    {
        if ($file instanceof LocalStorage) {
            $file = $file->getRealPath();
        }
        if ($keepOriginalName === true) {
            if ($file instanceof HandledStorage) {
                $originalName = basename($file->getRelativePath());
            }
            elseif ($file instanceof UploadedFile) {
                $originalName = $file->getClientOriginalName();
            }
            elseif ($file instanceof File) {
                $originalName = $file->getBasename();
            }
            else {
                $originalName = basename($file);
            }
            if ($file instanceof HandledStorage) {
                $path = trim(Helper::noWrappedSlashes($toDirectory) . '/' . $originalName, '/');
                $this->disk->put($path, $file->getContent(), $visibility);
                $this->relativePath = $path;
            }
            else {
                $this->relativePath = Helper::changeToPath($this->disk->putFileAs(Helper::noWrappedSlashes($toDirectory), $file, $originalName, $visibility));
            }
            return $this;
        }

        if ($file instanceof HandledStorage) {
            $extension = pathinfo($file->getRelativePath(), PATHINFO_EXTENSION);
            $path = trim(Helper::noWrappedSlashes($toDirectory) . '/' . Str::random(40) . ($extension ? '.' . $extension : ''), '/');
            $this->disk->put($path, $file->getContent(), $visibility);
            $this->relativePath = $path;
        }
        else {
            $this->relativePath = Helper::changeToPath($this->disk->putFile(Helper::noWrappedSlashes($toDirectory), $file, $visibility));
        }
        if ($keepOriginalName !== false) {
            $this->changeFilename($keepOriginalName);
        }
        return $this;
    }

    /**
     * @param $data
     * @return static
     */
    public function setData($data)
    {
        $this->relativePath = $data;
        return $this;
    }

    public function getData()
    {
        return $this->relativePath;
    }

    public function setContent($content)
    {
        $this->disk->put($this->relativePath, $content);
        return $this;
    }

    public function getContent()
    {
        return $this->getContentRelativePath();
    }

    public function getContentRelativePath($relativePath = null)
    {
        return $this->disk->get($relativePath ?: $this->getRelativePath());
    }

    public function getSize()
    {
        return $this->disk->getSize($this->relativePath);
    }

    public function getMime()
    {
        return $this->disk->getMimetype($this->relativePath);
    }

    public function getUrl()
    {
        return Helper::changeToUrl(urldecode($this->disk->url($this->relativePath)));
    }

    public function write($contents)
    {
        $this->disk->put($this->relativePath, $contents);
    }

    public function append($contents, $separator = PHP_EOL)
    {
        $this->disk->append($this->relativePath, $contents, $separator);
    }

    public function prepend($contents, $separator = PHP_EOL)
    {
        $this->disk->prepend($this->relativePath, $contents, $separator);
    }

    /**
     * @param string $toDirectory
     * @param bool|string|array $keepOriginalName
     * @param bool $override
     * @param callable|null $overrideCallback
     * @return static
     * @throws
     */
    public function move($toDirectory = '', $keepOriginalName = true, $override = true, callable $overrideCallback = null)
    {
        return $this->fromTo(function ($storage, $from, $to) {
            $this->disk->move($from, $to);
            $this->relativePath = $to;
        }, $toDirectory, $keepOriginalName, $override, $overrideCallback);
    }

    /**
     * @param string $toDirectory
     * @param bool|string|array $keepOriginalName
     * @param bool $override
     * @param callable|null $overrideCallback
     * @return static
     * @throws
     */
    public function copy($toDirectory = '', $keepOriginalName = true, $override = true, callable $overrideCallback = null)
    {
        return $this->fromTo(function ($storage, $from, $to) {
            $this->disk->copy($from, $to);
            $this->relativePath = $to;
        }, $toDirectory, $keepOriginalName, $override, $overrideCallback);
    }

    /**
     * @param callable $callback
     * @param string $toDirectory
     * @param bool|string|array $keepOriginalName
     * @param bool $override
     * @param callable|null $overrideCallback
     * @return static
     * @throws
     */
    public function fromTo(callable $callback, $toDirectory = '', $keepOriginalName = true, $override = true, callable $overrideCallback = null)
    {
        if (!is_null($toDirectory) || $keepOriginalName !== true) {
            $toDirectory = is_null($toDirectory) ? $this->getRelativeDirectory() : Helper::noWrappedSlashes($toDirectory);
            if ($keepOriginalName === true) {
                $toFilename = $this->getBasename();
            }
            else {
                $toFilename = is_array($keepOriginalName) ?
                    Helper::nameWithExtension(
                        $keepOriginalName['name'] ?? null,
                        $keepOriginalName['extension'] ?? $this->getExtension()
                    )
                    : Helper::nameWithExtension(
                        is_string($keepOriginalName) ? $keepOriginalName : null,
                        $this->getExtension()
                    );
            }
            $relativePath = Helper::concatPath($toDirectory, $toFilename);
            if ($this->exists($relativePath)) {
                if ($override) {
                    (new static())->setRelativePath($relativePath)->delete();
                }
                else {
                    if ($overrideCallback) {
                        $overrideCallback();
                    }
                    throw new AppException('Overriding file was not allowed');
                }
            }
            $callback($this, $this->relativePath, $relativePath);
        }
        return $this;
    }

    /**
     * @param string|array $filename
     * @return static
     */
    public function changeFilename($filename)
    {
        return $this->move(null, $filename);
    }

    public function delete()
    {
        return $this->deleteRelativePath();
    }

    public function deleteRelativePath($relativePath = null)
    {
        $this->disk->delete($relativePath ?: $this->getRelativePath());
        return $this;
    }

    public function deleteRelativeDirectory($relativeDirectory = null)
    {
        $this->disk->deleteDirectory($relativeDirectory ?: $this->getRelativeDirectory());
        return $this;
    }

    public function exists($relativePath)
    {
        return $this->disk->exists($relativePath);
    }

    public function makeDirectory($relativeDirectory)
    {
        $this->disk->makeDirectory($relativeDirectory);
        return $this;
    }

    public function first(callable $conditionCallback, $inRelativeDirectory = '', $all = false)
    {
        return $this->find($conditionCallback, $inRelativeDirectory, $all)->first();
    }

    public function find(callable $conditionCallback, $inRelativeDirectory = '', $all = false)
    {
        return collect($this->disk->files($inRelativeDirectory, $all))->filter($conditionCallback);
    }

    public function responseFile($mime, $headers = [])
    {
        if ($this->encrypted()) {
            return response()->streamDownload(function () {
                $this->streamDecrypt();
            }, null, array_merge([
                'Content-Type' => $mime,
            ], $headers), 'inline');
        }
        return $this->disk->response($this->relativePath, null, $headers);
    }

    public function responseDownload($name, $mime, $headers = [])
    {
        if ($this->encrypted()) {
            return response()->streamDownload(function () {
                $this->streamDecrypt();
            }, $name, array_merge([
                'Content-Type' => $mime,
            ], $headers));
        }
        return $this->disk->download($this->relativePath, $name, $headers);
    }
}
