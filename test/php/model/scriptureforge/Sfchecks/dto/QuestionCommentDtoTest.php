<?php

use Api\Library\Shared\Palaso\Exception\ResourceNotAvailableException;
use Api\Model\Scriptureforge\Sfchecks\AnswerModel;
use Api\Model\Scriptureforge\Sfchecks\Dto\QuestionCommentDto;
use Api\Model\Scriptureforge\Sfchecks\QuestionModel;
use Api\Model\Scriptureforge\Sfchecks\TextModel;
use Api\Model\Shared\CommentModel;
use Api\Model\Shared\Rights\ProjectRoles;
//use PHPUnit\Framework\TestCase;

class QuestionCommentDtoTest extends PHPUnit_Framework_TestCase
{
    /** @var MongoTestEnvironment Local store of mock test environment */
    private static $environ;

    public static function setUpBeforeClass()
    {
        self::$environ = new MongoTestEnvironment();
        self::$environ->clean();
    }

    /**
     * Cleanup test environment
     */
    public function tearDown()
    {
        self::$environ->clean();
    }

    public function testEncode_FullQuestionWithAnswersAndComments_DtoReturnsExpectedData()
    {
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);

        $text = new TextModel($project);
        $text->title = "Text 1";
        $usx = MongoTestEnvironment::usxSample();
        $text->content = $usx;
        $textId = $text->write();

        $user1Id = self::$environ->createUser("user1", "user1", "user1@email.com");
        $user2Id = self::$environ->createUser("user2", "user2", "user2@email.com");
        $user3Id = self::$environ->createUser("user3", "user3", "user3@email.com");

        // Workflow is first to create a question
        $question = new QuestionModel($project);
        $question->title = "the question";
        $question->description = "question description";
        $question->textRef->id = $textId;
        $questionId = $question->write();

        // Then to add an answer to a question
        $answer = new AnswerModel();
        $answer->content = "first answer";
        $answer->score = 10;
        $answer->userRef->id = $user3Id;
        $answer->textHightlight = "text highlight";
        $answerId = $question->writeAnswer($answer);

        // Followed by comments
        $comment1 = new CommentModel();
        $comment1->content = "first comment";
        $comment1->userRef->id = $user1Id;
        $comment1Id = QuestionModel::writeComment($project->databaseName(), $questionId, $answerId, $comment1);

        $comment2 = new CommentModel();
        $comment2->content = "second comment";
        $comment2->userRef->id = $user2Id;
        $comment2Id = QuestionModel::writeComment($project->databaseName(), $questionId, $answerId, $comment2);

        $dto = QuestionCommentDto::encode($project->id->asString(), $questionId, $user1Id);
        //var_dump($dto);

        $aid = $answerId;
        $cid1 = $comment1Id;
        $cid2 = $comment2Id;
        $this->assertEquals($project->id, $dto['project']['id']);
        //$this->assertEquals($text->content, $dto['text']['content']);
        $this->assertEquals($questionId, $dto['question']['id']);
        $this->assertEquals('the question', $dto['question']['title']);
        $this->assertEquals('question description', $dto['question']['description']);
        $this->assertEquals('first answer', $dto['question']['answers'][$aid]['content']);
        $this->assertEquals(10, $dto['question']['answers'][$aid]['score']);
        $this->assertEquals('user3.png', $dto['question']['answers'][$aid]['userRef']['avatar_ref']);
        $this->assertEquals('user3', $dto['question']['answers'][$aid]['userRef']['username']);
        $this->assertEquals('first comment', $dto['question']['answers'][$aid]['comments'][$cid1]['content']);
        $this->assertEquals('user1', $dto['question']['answers'][$aid]['comments'][$cid1]['userRef']['username']);
        $this->assertEquals('user1.png', $dto['question']['answers'][$aid]['comments'][$cid1]['userRef']['avatar_ref']);
        $this->assertEquals('second comment', $dto['question']['answers'][$aid]['comments'][$cid2]['content']);
        $this->assertEquals('user2', $dto['question']['answers'][$aid]['comments'][$cid2]['userRef']['username']);
        $this->assertEquals('user2.png', $dto['question']['answers'][$aid]['comments'][$cid2]['userRef']['avatar_ref']);
    }

    /**
     * @expectedException Api\Library\Shared\Palaso\Exception\ResourceNotAvailableException
     */
    public function testEncode_ArchivedQuestion_ManagerCanViewContributorCannot()
    {
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);

        $managerId = self::$environ->createUser("manager", "manager", "manager@email.com");
        $contributorId = self::$environ->createUser("contributor1", "contributor1", "contributor1@email.com");
        $project->addUser($managerId, ProjectRoles::MANAGER);
        $project->addUser($contributorId, ProjectRoles::CONTRIBUTOR);
        $project->write();

        // Text not archived but Question is archived
        $text = new TextModel($project);
        $text->title = "Text 1";
        $textId = $text->write();

        $question = new QuestionModel($project);
        $question->title = "the question";
        $question->description = "question description";
        $question->textRef->id = $textId;
        $question->isArchived = true;
        $questionId = $question->write();

        $dto = QuestionCommentDto::encode($project->id->asString(), $questionId, $managerId);

        // Manager can view Question of archived Text
        $this->assertEquals('the question', $dto['question']['title']);

        // Contributor cannot view Question of archived Text, throw Exception
        self::$environ->inhibitErrorDisplay();

        QuestionCommentDto::encode($project->id->asString(), $questionId, $contributorId);

        // nothing runs in the current test function after an exception. IJH 2014-11
    }
    /**
     * @depends testEncode_ArchivedQuestion_ManagerCanViewContributorCannot
     */
    public function testEncode_ArchivedQuestion_ManagerCanViewContributorCannot_RestoreErrorDisplay()
    {
        // restore error display after last test
        self::$environ->restoreErrorDisplay();
    }

    /**
     * @expectedException Api\Library\Shared\Palaso\Exception\ResourceNotAvailableException
     */
    public function testEncode_ArchivedText_ManagerCanViewContributorCannot()
    {
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);

        $managerId = self::$environ->createUser("manager", "manager", "manager@email.com");
        $contributorId = self::$environ->createUser("contributor1", "contributor1", "contributor1@email.com");
        $project->addUser($managerId, ProjectRoles::MANAGER);
        $project->addUser($contributorId, ProjectRoles::CONTRIBUTOR);
        $project->write();

        // Question not archived but Text is archived
        $text = new TextModel($project);
        $text->title = "Text 1";
        $text->isArchived = true;
        $textId = $text->write();

        $question = new QuestionModel($project);
        $question->title = "the question";
        $question->description = "question description";
        $question->textRef->id = $textId;
        $questionId = $question->write();

        $dto = QuestionCommentDto::encode($project->id->asString(), $questionId, $managerId);

        // Manager can view Question of archived Text
        $this->assertEquals('the question', $dto['question']['title']);

        // Contributor cannot view Question of archived Text, throw Exception
        self::$environ->inhibitErrorDisplay();

        QuestionCommentDto::encode($project->id->asString(), $questionId, $contributorId);

        // nothing runs in the current test function after an exception. IJH 2014-11
    }
    /**
     * @depends testEncode_ArchivedText_ManagerCanViewContributorCannot
     */
    public function testEncode_ArchivedText_ManagerCanViewContributorCannot_RestoreErrorDisplay()
    {
        // restore error display after last test
        self::$environ->restoreErrorDisplay();
    }
}
