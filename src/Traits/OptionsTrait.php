<?php

namespace Questionnaire\Traits;

trait OptionsTrait
{

    public function hasOptions()
    {
        if ( ! $this->options) {
            return false;
        }
        
        return $this->options->isNotEmpty();
    }

    public function hasOption($key, $value = null)
    {
        if ($this->hasOptions()) {
            if ($value) {
                foreach ($this->options as $optionKey => $optionValue) {
                    if ($optionKey == $key && $optionValue == $value) {
                        return true;
                    }
                }
            } else {
                foreach ($this->options as $optionKey => $optionValue) {
                    if ($optionKey == $key) {
                        return true;
                    }
                }
            }

            return false;

            // somehow these collection methods do not work as expected, don't have the time to debug
            if ($value) {
                return $this->options->has([$key, $value]);
            }

            return $this->options->has($key);
        }

        return false;
    }

    public function getOption($key, $defaultValue = null)
    {
        if ($this->hasOptions()) {
            return $this->options->get($key, $defaultValue);
        }

        return $defaultValue;
    }

    public function addOption($key, $value)
    {
        if ( ! $this->hasOptions()) {
            $this->options = collect([]);
        }

        return $this->options->put($key, $value);
    }

}
