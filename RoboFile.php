<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
use Symfony\Component\Finder\Finder;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {
    
    public function phar() {
        // based on example in http://robo.li/tasks/Development/#packphar
        
        $pharTask = $this->taskPackPhar('CloudObjects-CLI.phar')
            ->compress()
            ->addFile('cloudobjects.php', 'cloudobjects.php')
            ->stub('stub.php');

        $finder = Finder::create()
            ->name('*.php')
            ->in('classes');

        foreach ($finder as $file) {
            $pharTask->addFile('classes/'.$file->getRelativePathname(), $file->getRealPath());
        }

        $finder = Finder::create()->files()
            ->name('*.php')
            ->in('vendor');

        foreach ($finder as $file) {
            $pharTask->addStripped('vendor/'.$file->getRelativePathname(), $file->getRealPath());
        }
        $pharTask->run();

        // Verify Phar is packed correctly
        $code = $this->_exec('php CloudObjects-CLI-alt.phar');
    }

}