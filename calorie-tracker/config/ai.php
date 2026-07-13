<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monthly OpenAI call cap
    |--------------------------------------------------------------------------
    |
    | A hard ceiling on the number of OpenAI calls the whole app may make in a
    | calendar month, across every user. It is a safety net against a runaway
    | loop or abuse quietly running up the bill — OpenAI's own billing limit is
    | the outer guard; this is the cheap, app-level one that fails gracefully.
    |
    | Set to 0 to disable the cap (unlimited). gpt-4o-mini is inexpensive, so
    | the default is generous; lower it if you want a tighter guarantee.
    |
    */

    'monthly_call_cap' => (int) env('AI_MONTHLY_CALL_CAP', 5000),

];
