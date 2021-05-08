<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Utils\HandledFiles\Filer;

trait WriteFilerTrait
{
    protected $fNotWritten = true;

    /**
     * @return Filer|mixed
     */
    public function fStartWriting()
    {
        return $this->fOpen();
    }

    /**
     * @return Filer|mixed
     */
    public function fStartAppending()
    {
        return $this->fOpen(Filer::MODE_WRITE_APPEND);
    }

    /**
     * @param array|mixed $contents
     * @param int|null $length
     * @return Filer|mixed
     */
    public function fWrite($contents, $length = null)
    {
        return $this->fWriting($contents, function ($contents) use ($length) {
            $length ? fwrite($this->fResource, $contents, $length)
                : fwrite($this->fResource, $contents);
        });
    }

    /**
     * @param array|mixed $contents
     * @param callable $callback
     * @return Filer|mixed
     */
    protected function fWriting($contents, callable $callback)
    {
        if (is_resource($this->fResource)) {
            $contents = $this->fBeforeWriting($contents);
            foreach ((array)$contents as $content) {
                $callback($content);
            }
            $this->fAfterWriting($contents);
        }
        return $this;
    }

    protected function fBeforeWriting($contents)
    {
        return $contents;
    }

    protected function fAfterWriting($contents)
    {
        $this->fNotWritten = false;
        $this->fNewly = false;
    }

    /**
     * @return Filer|mixed
     */
    public function fEndWriting()
    {
        $this->fClose();
        $this->fNotWritten = true;
        return $this;
    }
}
