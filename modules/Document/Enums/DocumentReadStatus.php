<?php

namespace Modules\Document\Enums;

enum DocumentReadStatus: string
{
    case Read = 'read';
    case Unread = 'unread';
}
