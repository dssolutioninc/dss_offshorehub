<?php namespace Kenshin\Contact;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Kenshin\Contact\Components\ContactForm' =>'contactform',
            
        ];
    }

    public function registerSettings()
    {
    }
}
