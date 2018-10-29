<?php
namespace Kenshin\Contact\Components;
use Cms\Classes\ComponentBase;
use Input;
use Mail;
class ContactForm extends \Cms\Classes\ComponentBase
{
public function componentDetails(){
    return [
        'name' => 'Contact Form',
        'desctiption' => 'Simple contact form'
    ];
}
public function onSend(){
    // These variables are available inside the message as Twig
$vars = ['name' => 'Joe', 'user' => 'Mary'];
//print_r($vars);
Mail::send('kenshin.contact::mail.message', $vars, function($message) {

    $message->to('itplusvn@gmail.com', 'Admin Person');
    $message->subject('This is a reminder');
    
});
}
}
