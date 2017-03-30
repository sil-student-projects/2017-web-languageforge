<?php

namespace Api\Model\Languageforge\Lexicon\Command;

use Litipk\Jiffy\UniversalTimestamp;
use Palaso\Utilities\CodeGuard;
use Api\Model\Languageforge\Lexicon\LexCommentModel;
use Api\Model\Languageforge\Lexicon\LexCommentReply;
use Api\Model\Languageforge\Lexicon\LexProjectModel;
use Api\Model\Languageforge\Lexicon\LexCommentListModel;
use Api\Model\Shared\Command\ProjectCommands;
use Api\Model\Shared\Dto\RightsHelper;
use Api\Model\Shared\Mapper\JsonDecoder;
use Api\Model\Shared\Rights\Domain;
use Api\Model\Shared\Rights\Operation;
use Api\Model\Shared\UserGenericVoteModel;


class LexCommentCommands
{
    public static function updateComment($projectId, $userId, $website, $params)
    {
        CodeGuard::checkTypeAndThrow($params, 'array');
        $project = new LexProjectModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($project);
        $rightsHelper = new RightsHelper($userId, $project, $website);
        $isNew = ($params['id'] == '');
        if ($isNew) {
            $comment = new LexCommentModel($project);
        } else {
            $comment = new LexCommentModel($project, $params['id']);
            if ($comment->authorInfo->createdByUserRef->asString() != $userId && !$rightsHelper->userHasProjectRight(Domain::COMMENTS + Operation::EDIT)) {
                throw new \Exception("No permission to update other people's lex comments!");
            }

            // don't allow setting these on update
            unset($params['regarding']);
            unset($params['entryRef']);
        }

        JsonDecoder::decode($comment, $params);

        if ($isNew) {
            $comment->authorInfo->createdByUserRef->id = $userId;
            $comment->authorInfo->createdDate = UniversalTimestamp::now();
        }
        $comment->authorInfo->modifiedByUserRef->id = $userId;
        $comment->authorInfo->modifiedDate = UniversalTimestamp::now();

        return $comment->write();
    }

    public static function updateReply($projectId, $userId, $website, $commentId, $params)
    {
        CodeGuard::checkTypeAndThrow($params, 'array');
        CodeGuard::checkEmptyAndThrow($commentId, 'commentId in updateReply()');
        $project = new LexProjectModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($project);
        $comment = new LexCommentModel($project, $commentId);
        $rightsHelper = new RightsHelper($userId, $project, $website);
        $replyId = $params['id'];
        if (array_key_exists('id', $params) && $replyId != '') {
            $reply = $comment->getReply($replyId);
            if ($reply->authorInfo->createdByUserRef->asString() != $userId && !$rightsHelper->userHasProjectRight(Domain::COMMENTS + Operation::EDIT)) {
                throw new \Exception("No permission to update other people's lex comment replies!");
            }
            if ($reply->content != $params['content']) {
                $reply->authorInfo->modifiedDate = UniversalTimestamp::now();
            }
            $reply->content = $params['content'];
            $comment->setReply($replyId, $reply);
        } else {
            $reply = new LexCommentReply();
            $reply->content = $params['content'];
            $reply->authorInfo->createdByUserRef->id = $userId;
            $reply->authorInfo->modifiedByUserRef->id = $userId;
            $now = UniversalTimestamp::now();
            $reply->authorInfo->createdDate = $now;
            $reply->authorInfo->modifiedDate = $now;
            $comment->replies->append($reply);
            $replyId = $reply->id;
        }
        $comment->write();

        return $replyId;
    }

    public static function plusOneComment($projectId, $userId, $commentId)
    {
        $project = new LexProjectModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($project);
        $comment = new LexCommentModel($project, $commentId);

        $vote = new UserGenericVoteModel($userId, $projectId, 'lexCommentPlusOne');
        if ($vote->hasVote($commentId)) {
            return false;
        }

        $comment->score++;
        $id = $comment->write();
        $vote->addVote($commentId);
        $vote->write();

        return $id;
    }

    public static function getCommentsByWordId($projectId, $wordId)
    {
        $project = new LexProjectModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($project);

        $commentsModel = new LexCommentListModel($project, null);
        $commentsModel->readAsModels();

        $comments = array();

        foreach($commentsModel->entries as $entry){
            if($entry->entryRef->id == $wordId){
                $comments[$entry->id->id]=$entry->content;
            }
        }
        return $comments;

    }

    public static function updateCommentStatus($projectId, $commentId, $status)
    {
        if (in_array($status, array(LexCommentModel::STATUS_OPEN, LexCommentModel::STATUS_RESOLVED, LexCommentModel::STATUS_TODO))) {
            $project = new LexProjectModel($projectId);
            ProjectCommands::checkIfArchivedAndThrow($project);
            $comment = new LexCommentModel($project, $commentId);

            $comment->status = $status;
            return $comment->write();
        } else {
            throw new \Exception("unknown status type: $status");
        }
    }

    public static function vote($projectId, $wordID, $userId, $website,$commentText)
    {
        $commentID = null;
        $comments =  LexCommentCommands::getCommentsByWordId($projectId,$wordID);
        foreach($comments as $id => $text){
            if($text == $commentText) $commentID = $id;
        }

        if($commentID == null){
            $data = array("id"=>"","content"=>$commentText,"entryRef"=>$wordID);
            return LexCommentCommands::updateComment($projectId, $userId, $website, $data);
        }else{
            return LexCommentCommands::plusOneComment($projectId, $userId,$commentID);
        }
    }

    /**
     * @param  string $projectId
     * @param  string $userId
     * @param  \Api\Library\Shared\Website $website
     * @param  string $commentId
     * @throws \Exception
     * @return string $commentId
     */
    public static function deleteComment($projectId, $userId, $website, $commentId)
    {
        // user must have DELETE_OWN privilege just to access this method

        $project = new LexProjectModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($project);
        $comment = new LexCommentModel($project, $commentId);
        if ($comment->authorInfo->createdByUserRef->asString() != $userId) {

            // if the userId is different from the author, throw if user does not have DELETE privilege
            $rh = new RightsHelper($userId, $project, $website);
            if (!$rh->userHasProjectRight(Domain::COMMENTS + Operation::DELETE)) {
                throw new \Exception("No permission to delete other people's comments!");
            }
        }
        return LexCommentModel::remove($project, $commentId);
    }

    /**
     * @param  string $projectId
     * @param  string $userId
     * @param  \Api\Library\Shared\Website $website
     * @param  string $commentId
     * @param  string $replyId
     * @throws \Exception
     * @return string $commentId
     */
    public static function deleteReply($projectId, $userId, $website, $commentId, $replyId)
    {
        // if the userId is different from the author, throw if user does not have DELETE privilege
        $project = new LexProjectModel($projectId);
        ProjectCommands::checkIfArchivedAndThrow($project);
        $comment = new LexCommentModel($project, $commentId);
        $reply = $comment->getReply($replyId);
        if ($reply->authorInfo->createdByUserRef->asString() != $userId) {

            // if the userId is different from the author, throw if user does not have DELETE privilege
            $rh = new RightsHelper($userId, $project, $website);
            if (!$rh->userHasProjectRight(Domain::COMMENTS + Operation::DELETE)) {
                throw new \Exception("No permission to delete other people's comment replies!");
            }
        }
        $comment->deleteReply($replyId);
        return $comment->write();
    }

}
