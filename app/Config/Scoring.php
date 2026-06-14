<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Scoring extends BaseConfig
{
    /**
     * Developer Option Passcode
     * Used for password-protected quick access to manual hukuman/binaan input
     * (ketua pertandingan dewan mode). Load from .env: scoring.developerPasscode
     */
    public string $developerPasscode = '4321';
}
