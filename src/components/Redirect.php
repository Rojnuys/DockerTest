<?php

class Redirect
{
    static public function path($path)
    {
        header("Location: $path");
        exit();
    }
}