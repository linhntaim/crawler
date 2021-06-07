<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Mail\Base;

abstract class TemplateNowMailable extends NowMailable
{
    public $emailLocalized = true;

    public $emailNamespace;

    public $emailView;

    public function build()
    {
        return parent::build()->view($this->getEmailView(), [
            'locale' => $this->locale,
            'charset' => $this->htmlCharset,
        ]);
    }

    /**
     * @param bool $emailLocalized
     * @return static
     */
    public function setEmailLocalized(bool $emailLocalized)
    {
        $this->emailLocalized = $emailLocalized;
        return $this;
    }

    /**
     * @param string $emailNamespace
     * @return static
     */
    public function setEmailNamespace(string $emailNamespace)
    {
        $this->emailNamespace = $emailNamespace;
        return $this;
    }

    /**
     * @param string $emailView
     * @return static
     */
    public function setEmailView(string $emailView)
    {
        $this->emailView = $emailView;
        return $this;
    }

    public function getEmailView()
    {
        return sprintf('%semails.%s%s',
            $this->emailNamespace ? $this->emailNamespace . '::' : '',
            $this->emailView,
            ($this->emailLocalized ? '.' . $this->locale : '')
        );
    }
}