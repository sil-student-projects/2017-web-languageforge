<?php

namespace Api\Model\Command;

use Api\Library\Shared\Palaso\Exception\ResourceNotAvailableException;
use Api\Library\Shared\Palaso\Exception\UserUnauthorizedException;
use Api\Library\Shared\Website;
use Api\Model\EmailSettings;
use Api\Model\Languageforge\Lexicon\Command\SendReceiveCommands;
use Api\Model\Shared\Dto\ManageUsersDto;
use Api\Model\Shared\Rights\ProjectRoles;
use Api\Model\Mapper\JsonDecoder;
use Api\Model\Mapper\JsonEncoder;
use Api\Model\Mapper\MongoStore;
use Api\Model\ProjectListModel;
use Api\Model\ProjectModel;
use Api\Model\ProjectSettingsModel;
use Api\Model\Shared\Rights\SystemRoles;
use Api\Model\Sms\SmsSettings;
use Api\Model\UserModel;
use MongoDB\BSON\UTCDateTime;
use Palaso\Utilities\CodeGuard;

class ProjectCommands
{
    /**
     * Create a project, checking permissions as necessary
     * @param string $projectName
     * @param string $projectCode
     * @param string $appName
     * @param string $userId
     * @param Website $website
     * @param array $srProject send receive project data
     * @return string - projectId
     */
    public static function createProject($projectName, $projectCode, $appName, $userId, $website, $srProject = null)
    {
        // Check for unique project code
        if (ProjectCommands::projectCodeExists($projectCode)) {
            return false;
        }
        $project = new ProjectModel();
        $project->projectName = $projectName;
        $project->projectCode = $projectCode;
        $project->appName = $appName;
        $project->siteName = $website->domain;
        $project->ownerRef->id = $userId;
        $project->addUser($userId, ProjectRoles::MANAGER);
        $projectId = $project->write();
        if ($srProject) {
            SendReceiveCommands::updateSRProject($projectId, $srProject);
        }
        $user = new UserModel($userId);
        $user->addProject($projectId);
        $user->write();

        $project = ProjectModel::getById($projectId);
        $project->initializeNewProject();
        ActivityCommands::addUserToProject($project, $userId);

        return $projectId;
    }

    /**
     * @param string $id
     * @return array
     */
    public static function readProject($id)
    {
        $project = new ProjectModel($id);

        return JsonEncoder::encode($project);
    }

    /**
     * Delete a list of projects
     * @param array $projectIds
     * @return int Total number of projects removed.
     */
    public static function deleteProjects($projectIds)
    {
        CodeGuard::checkTypeAndThrow($projectIds, 'array');
        $count = 0;
        foreach ($projectIds as $projectId) {
            CodeGuard::checkTypeAndThrow($projectId, 'string');
            $project = new ProjectModel($projectId);
            $project = $project->getById($projectId);
            $project->remove();
            $count++;
        }
        return $count;
    }

    /**
     * Archive a project, append a UTC timestamp to the project code and project name
     * @param string $projectId
     * @param string $userId
     * @return string projectId of the project archived.
     * @throws UserUnauthorizedException
     */
    public static function archiveProject($projectId, $userId)
    {
        CodeGuard::checkTypeAndThrow($projectId, 'string');
        CodeGuard::checkTypeAndThrow($userId, 'string');

        $project = new ProjectModel($projectId);
        $user = new UserModel($userId);
        if ($userId != $project->ownerRef->asString() && $user->role != SystemRoles::SYSTEM_ADMIN) {
            throw new UserUnauthorizedException(
                "This $project->appName project '$project->projectName'\n" .
                "can only be archived by project owner or\n " .
                "a system administrator\n");
        }

        $user->lastUsedProjectId = '';
        $user->write();

        $project->isArchived = true;

        // Append UTC timestamp to projectCode and projectName
        do {
            $archiveDate = gmdate('Ymd\THis\Z', time());
            $newProjectCode = $project->projectCode . "_" . $archiveDate;
        }
        while (ProjectCommands::projectCodeExists($newProjectCode));
        $project->renameProjectCode($newProjectCode);
        $project->projectName .= " [ARCHIVED $archiveDate]";
        return $project->write();
    }

    public static function projectCodeRegexPattern()
    {
        return '/_\d{8}T\d{6}Z$/';
    }

    public static function projectNameRegexPattern()
    {
        return '/ \[ARCHIVED \d{8}T\d{6}Z\]$/';
    }

    /**
     * @param array $projectIds
     * @return int Total number of projects published.
     */
    public static function publishProjects($projectIds)
    {
        CodeGuard::checkTypeAndThrow($projectIds, 'array');
        $count = 0;
        foreach ($projectIds as $projectId) {
            CodeGuard::checkTypeAndThrow($projectId, 'string');
            $project = new ProjectModel($projectId);
            $project->isArchived = false;

            // If projectCode sans UTC timestamp doesn't exist, remove timestamp from the projectCode.
            // Otherwise, leave the projectCode intact.
            $newProjectCode = preg_replace(ProjectCommands::projectCodeRegexPattern(), '', $project->projectCode, 1, $modifyCount);
            if (($modifyCount == 1) && !ProjectCommands::ProjectCodeExists($newProjectCode)) {
                $project->projectCode = $newProjectCode;
            }
            // Similar for UTC timestamp and projectName
            $archiveDateTime = 0;
            preg_match(ProjectCommands::projectNameRegexPattern(), $project->projectName, $matches);
            if (count($matches) > 0) {
                $archiveDateTime = preg_replace('/ \[ARCHIVED /', '', $matches[0], 1, $modifyCount);
                $archiveDateTime = preg_replace('/\]$/', '', $archiveDateTime, 1, $modifyCount);
            }
            $newProjectName = preg_replace(ProjectCommands::projectNameRegexPattern(), '', $project->projectName, 1, $modifyCount);
            if ($modifyCount == 1) {
                if (!ProjectCommands::ProjectNameExists($newProjectName)) {
                    $project->projectName = $newProjectName;
                }
                // If the projectName already exists, keep the archive timestamp on the new project name
                else {
                    $project->projectName = $newProjectName . " [$archiveDateTime]";
                }
            }

            $project->write();
            $count++;
        }

        return $count;
    }

    /**
     *
     * @return \Api\Model\ProjectListModel
     */
    public static function listProjects()
    {
        $list = new ProjectListModel();
        $list->read();

        return $list;
    }

    /**
     * If the project is archived, throws an exception because the project should not be modified
     * @param ProjectModel $project
     * @throws ResourceNotAvailableException
     */
    public static function checkIfArchivedAndThrow($project) {
        CodeGuard::checkNullAndThrow($project, 'project');
        CodeGuard::checkTypeAndThrow($project, '\Api\Model\ProjectModel');
        if ($project->isArchived) {
            throw new ResourceNotAvailableException(
                "This $project->appName project '$project->projectName'\n" .
                "is archived and cannot be modified. Please\n" .
                "contact a system administrator to re-publish\n" .
                "this project if you want to make further updates.");
        }
    }

    /**
     * List users in the project
     * @param string $projectId
     * @return array - the DTO array
     */
    public static function usersDto($projectId)
    {
        CodeGuard::checkTypeAndThrow($projectId, 'string');
        CodeGuard::checkNotFalseAndThrow($projectId, '$projectId');

        $usersDto = ManageUsersDto::encode($projectId);

        return $usersDto;
    }

    /**
     * Gets list of user requests
     * @param string $projectId
     * @return array of users join requests
     */
    public static function getJoinRequests($projectId) 
    {        
        $projectModel = ProjectModel::getById($projectId);        
        $list = $projectModel->listRequests();
        return $list;
    }

    /**
     * Update the user project role in the project
     * @param string $projectId
     * @param string $userId
     * @param string $projectRole
     * @throws \Exception
     * @return string $userId
     */
    public static function updateUserRole($projectId, $userId, $projectRole = ProjectRoles::CONTRIBUTOR)
    {
        CodeGuard::checkNotFalseAndThrow($projectId, '$projectId');
        CodeGuard::checkNotFalseAndThrow($userId, 'userId');
        //CodeGuard::assertInArrayOrThrow($role, array(ProjectRoles::CONTRIBUTOR, ProjectRoles::MANAGER));

        // Add the user to the project
        $user = new UserModel($userId);
        $project = ProjectModel::getById($projectId);
        if ($project->userIsMember($userId) && $projectRole == $project->users[$userId]->role) {
            return $userId;
        }

        if ($userId == $project->ownerRef->asString()) {
            throw new \Exception("Cannot update role for project owner");
        }

        ProjectCommands::usersDto($projectId);
        if (!$project->userIsMember($userId)) {
            ActivityCommands::addUserToProject($project, $userId);
        }

        $project->addUser($userId, $projectRole);
        $user->addProject($projectId);
        $project->write();
        $user->write();

        return $userId;
    }

    /**
     * Removes users from the project (two-way unlink)
     * @param string $projectId
     * @param array $userIds array<string>
     * @return string $projectId
     * @throws \Exception
     */
    public static function removeUsers($projectId, $userIds)
    {
        $project = new ProjectModel($projectId);
        foreach ($userIds as $userId) {
            // Guard against removing project owner
            if ($userId != $project->ownerRef->id) {
                $user = new UserModel($userId);
                $project->removeUser($user->id->asString());
                $user->removeProject($project->id->asString());
                $project->write();
                $user->write();
            } else {
                throw new \Exception("Cannot remove project owner");
            }
        }

        return $projectId;
    }
    
    /**
     * Removes users from the project (two-way unlink)
     * @param string $projectId
     * @param string $joinRequestId
     * @return string $projectId
     */
    public static function removeJoinRequest($projectId, $joinRequestId)
    {
        $project = new ProjectModel($projectId);
        $project->removeUserJoinRequest($joinRequestId);
        return $project->write();
    }
    
    public static function grantAccessForUserRequest($projectId, $userId, $projectRole) {
        // check if userId exists in request queue on project model
        self::updateUserRole($projectId, $userId, $projectRole);
        // remove userId from request queue
        // send email notifying of acceptance
    }
    
    public static function requestAccessForProject($projectId, $userId) {
        // add userId to request queue
        // send email to project owner and all managers
    }
    
    

    public static function renameProject($projectId, $oldName, $newName)
    {
        // TODO: Write this. (Move renaming logic over from sf->project_update). RM 2013-08
    }

    public static function updateProjectSettings($projectId, $smsSettingsArray, $emailSettingsArray)
    {
        $projectSettings = new ProjectSettingsModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($projectSettings);
        $smsSettings = new SmsSettings();
        $emailSettings = new EmailSettings();
        JsonDecoder::decode($smsSettings, $smsSettingsArray);
        JsonDecoder::decode($emailSettings, $emailSettingsArray);
        $projectSettings->smsSettings = $smsSettings;
        $projectSettings->emailSettings = $emailSettings;
        $result = $projectSettings->write();

        return $result;
    }

    public static function readProjectSettings($projectId)
    {
        $project = new ProjectSettingsModel($projectId);

        return array(
            'sms' => JsonEncoder::encode($project->smsSettings),
            'email' => JsonEncoder::encode($project->emailSettings)
        );
    }

    /**
     * @param string $code
     * @return bool
     */
    public static function projectCodeExists($code)
    {
        $project = new ProjectModel();

        return $project->readByProperties(array('projectCode' => $code));
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function projectNameExists($name)
    {
        $project = new ProjectModel();

        return $project->readByProperties(array('projectName' => $name));
    }

}
