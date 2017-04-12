<?php

namespace Api\Model\Languageforge\Lexicon\Command;

use Api\Model\Languageforge\Lexicon\Config\LexConfig;
use Api\Model\Languageforge\Lexicon\Guid;
use Api\Model\Languageforge\Lexicon\LexEntryModel;
use Api\Model\Languageforge\Lexicon\LexEntryListModel;
use Api\Model\Languageforge\Lexicon\LexProjectModel;
use Api\Model\Shared\Command\ActivityCommands;
use Api\Model\Shared\Command\ProjectCommands;
use Api\Model\Shared\Mapper\JsonEncoder;
use Api\Model\Shared\ProjectModel;
use Litipk\Jiffy\UniversalTimestamp;
use Palaso\Utilities\CodeGuard;

class LexEntryCommands
{
    // Note: this is not actually used anymore...but we'll keep it around just in case - cjh 2014-07
    public static function readEntry($projectId, $entryId)
    {
        $project = new LexProjectModel($projectId);
        $entry = new LexEntryModel($project, $entryId);

        return JsonEncoder::encode($entry);
    }

    public static function readRandomEntrys($projectId, $numEntries)
    {
        $LexEntries = LexEntryCommands::listEntries($projectId);
        $word_list = [];
        foreach ($LexEntries->entries as $word){
            $word_list[$word["id"]] = LexEntryCommands::getWordDefs($projectId, $word["id"]);
        };

        while (count($word_list) > $numEntries){
            $rand_key = array_rand($word_list);
            unset($word_list[$rand_key]);
        };

        return $word_list;
    }

    public static function getWordDefs($projectId, $entryId)
    {
        $word = LexEntryCommands::readEntry($projectId, $entryId);
        $defLangs = LexProjectCommands::getDefinitionLanguages($projectId);
        $defs = array();
        foreach ($word["senses"] as $sence){
            $langs = array();
            foreach ($defLangs as $def){
                $langs[$def] = $sence["definition"][$def]["value"];
            }
            array_push($defs,$langs);
        }
        return array(array_pop($word["lexeme"])["value"] => $defs);
    }

    /*
    public static function addEntry($projectId, $params)
    {
        CodeGuard::checkTypeAndThrow($params, 'array');
        $project = new LexProjectModel($projectId);
        $entry = new LexEntryModel($project);
        JsonDecoder::decode($entry, $params);
        return $entry->write();
    }
    */

    /**
     * Updates the given LexEntry in $projectId
     * @param string $projectId
     * @param array $params
     * @param string $userId
     * @param string $mergeQueuePath
     * @param string $pidFilePath
     * @param string $command
     * @return bool|array<encoded LexEntryModel> if the project is syncing (or on hold) return false (no save)FixSe
     */
    public static function updateEntry($projectId, $params, $userId, $mergeQueuePath = null, $pidFilePath = null, $command = null)
    {
        CodeGuard::checkTypeAndThrow($params, 'array');
        $project = new LexProjectModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($project);
        $now = UniversalTimestamp::now();
        $project->lastEntryModifiedDate = $now;
        if (array_key_exists('id', $params) && $params['id'] != '') {
            $entry = new LexEntryModel($project, $params['id']);
            $action = 'update';
        } else {
            $entry = new LexEntryModel($project);
            $entry->authorInfo->createdByUserRef->id = $userId;
            $entry->authorInfo->createdDate = $now;
            $entry->guid = Guid::create();
            $action = 'create';
            // TODO: Consider adding more specific activity entry: which fields were modified? 2014-09-03 RM
            // E.g., "User _____ updated entry _____ by adding a new sense with definition ______"
        }

        $entry->authorInfo->modifiedDate = $now;
        $entry->authorInfo->modifiedByUserRef->id = $userId;

        if ($project->hasSendReceive()) {
//            $entry->dirtySR++;
            $entry->dirtySR = 0;
            if (SendReceiveCommands::isInProgress($projectId)) return false;
        }

        LexEntryDecoder::decode($entry, $params);

        $entry->write();
        $project->write();
        ActivityCommands::writeEntry($project, $userId, $entry, $action);

//        SendReceiveCommands::queueProjectForUpdate($project, $mergeQueuePath);
//        SendReceiveCommands::startLFMergeIfRequired($projectId, 'merge', $pidFilePath, $command);

        return JsonEncoder::encode($entry);
    }

    /**
     * @param string $projectId
     * @param string $missingInfo - if empty, returns all entries.
     *          if matches one of LexConfig constants (e.g. POS, DEFINITION, etc), then return a subset of entries that have one or more senses missing the specified field
     * @return LexEntryListModel
     */
    public static function listEntries($projectId, $missingInfo = '')
    {
        $project = new LexProjectModel($projectId);
        $lexEntries = new LexEntryListModel($project);
        $lexEntries->readForDto($missingInfo);

        return $lexEntries;
    }

    public static function removeEntry($projectId, $entryId, $userId)
    {
        $project = new ProjectModel($projectId);
        $entry = new LexEntryModel($project, $entryId);
        $entry->isDeleted = true;
        $entry->write();
        ActivityCommands::deleteEntry($project, $userId, $entryId);
        return true;
    }

    /**
     * @param string $projectId
     * @param string $entryId
     * @return string
     */
    public static function getEntryLexeme($projectId, $entryId) {
        $project = new LexProjectModel($projectId);
        $entry = new LexEntryModel($project, $entryId);
        $inputSystems = $project->config->entry->fields[LexConfig::LEXEME]->inputSystems;
        foreach ($inputSystems as $inputSystem) {
            if (array_key_exists($inputSystem, $entry->lexeme) && !empty($entry->lexeme[$inputSystem])) {
                return $entry->lexeme[$inputSystem]->value;
            }
        }
        return ''; // TODO: Decide what to return for "not found", if empty string is not suitable.
    }
}
