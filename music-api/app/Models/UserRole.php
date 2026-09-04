<?php

namespace App\Models;

enum UserRole: string
{
    case User = 'user';
    case Curator = 'curator';
    case Admin = 'admin';
}