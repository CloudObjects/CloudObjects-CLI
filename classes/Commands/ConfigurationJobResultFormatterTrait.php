<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
namespace CloudObjects\CLI\Commands;

use Symfony\Component\Console\Output\OutputInterface;

trait ConfigurationJobResultFormatterTrait {

    public function printResponse(array $response, OutputInterface $output) {
        if (isset($response['result']) && $response['result'] == 'success'
                && isset($response['created']) && is_array($response['created'])
                && isset($response['updated']) && is_array($response['updated'])
                && isset($response['notChanged']) && is_array($response['notChanged'])) {
            
            if (count($response['created']) > 0 || count($response['updated']) > 0)
                $output->writeln('<info>Configuration job was successful:</info>');
            else
                $output->writeln('Configuration job had no effect:');
            
            if (count($response['created']) == 1)
                $output->writeln(' - Created one new object: '.$response['created'][0]);
            elseif (count($response['created']) > 1)
                $output->writeln(' - Created '.count($response['created']).' new objects: '.implode(', ', $response['created']));

            if (count($response['updated']) == 1)
                $output->writeln(' - Updated one existing object: '.$response['updated'][0]);
            elseif (count($response['updated']) > 1)
                $output->writeln(' - Updated '.count($response['updated']).' existing objects: '.implode(', ', $response['updated']));

            if (count($response['notChanged']) == 1)
                $output->writeln(' - One object has no changes from the previous revision: '.$response['notChanged'][0]);
            elseif (count($response['notChanged']) > 1)
                $output->writeln(' - '.count($response['notChanged']).' objects have no changes from the previous revision: '.implode(', ', $response['notChanged']));

        } elseif (isset($response['result']) && $response['result'] == 'failure'
                && isset($response['errors']) && is_array($response['errors'])) {

            $output->writeln('<error>Errors in configuration job:</error>');
            foreach ($response['errors'] as $e)
                $output->writeln(' - '.$e['code'].': '.$e['message']);

        } else
            throw new \Exception("Unexpected API response could not be processed.");
    }

}