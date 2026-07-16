<?php

namespace App\Enums;

enum AttributeType: string
{
    case SELECT   = 'select';
    case TEXT     = 'text';
    case NUMBER   = 'number';
    case TEXTAREA = 'textarea';
    case RADIO    = 'radio';
    case CHECKBOX = 'checkbox';
    case COLOR    = 'color';
}
