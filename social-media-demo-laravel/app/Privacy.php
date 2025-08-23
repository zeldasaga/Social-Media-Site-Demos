<?php

namespace App;

// If using a non-backed enum is desired, comment the backing type of :string below
enum Privacy:string
{
    // Non-backed enums have a value assigned
    //  case Private;
    // case Public;
    // Backed enums have values and a declared type, such as string
    case Private = "private";
    case Public = "public";
}
