<?php

namespace Mougrim\Logger;

use QA\SoftMocks;

/**
 * @author Mougrim <rinat@mougrim.ru>
 */
class CodeCoverageFilter extends \PHP_CodeCoverage_Filter
{
    public function addFileToBlacklist($filename)
    {
        $filename = SoftMocks::rewrite($filename);
        parent::addFileToBlacklist($filename);
    }

    public function removeFileFromBlacklist($filename)
    {
        $filename = SoftMocks::rewrite($filename);
        parent::removeFileFromBlacklist($filename);
    }

    public function addFileToWhitelist($filename)
    {
        $filename = SoftMocks::rewrite($filename);
        parent::addFileToWhitelist($filename);
    }

    public function removeFileFromWhitelist($filename)
    {
        $filename = SoftMocks::rewrite($filename);
        parent::removeFileFromWhitelist($filename);
    }
}
