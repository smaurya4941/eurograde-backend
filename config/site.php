<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Website forms
    |--------------------------------------------------------------------------
    | Where new contact-form enquiries are emailed. Leave empty to only store
    | them in the database (e.g. in local development).
    */

    'contact' => [
        'notify_email' => env('CONTACT_NOTIFY_EMAIL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog comments
    |--------------------------------------------------------------------------
    | New comments are held for moderation (status "pending") until approved.
    | The office is emailed about each one at contact.notify_email.
    */
    'comments' => [
        'require_approval' => (bool) env('COMMENTS_REQUIRE_APPROVAL', true),
    ],

];
