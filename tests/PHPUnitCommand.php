<?php

namespace Mougrim\Logger;

/**
 * @author Mougrim <rinat@mougrim.ru>
 */
class PHPUnitCommand extends \PHPUnit_TextUI_Command
{
    protected function createRunner()
    {
        $filter = new CodeCoverageFilter();

        return new \PHPUnit_TextUI_TestRunner($this->arguments['loader'], $filter);
    }
}
