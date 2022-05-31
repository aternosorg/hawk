<?php

namespace Aternos\Hawk\Enums;

/**
 * SEEK_SET - Set position equal to offset bytes.
 * SEEK_CUR - Set position to current location plus offset.
 * SEEK_END - Set position to end-of-file plus offset.
 */
class SeekType
{
    const SEEK_SET = 0;
    const SEEK_CUR = 1;
    const SEEK_END = 2;
}