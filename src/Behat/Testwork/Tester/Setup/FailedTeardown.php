<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Setup;

/**
 * Testwork failed teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FailedTeardown implements Teardown
{
    /**
     * Returns true if fixtures have been handled successfully.
     *
     * @return Boolean
     */
    public function isSuccessful()
    {
        return false;
    }
}
