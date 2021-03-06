<?php

namespace frontend\controllers;

use common\components\extended\Controller;
use common\models\Error;
use common\models\Quiz;
use common\models\QuizResult;
use common\models\Subject;
use common\models\Webpage;
use yii;

/**
 * QuizController implements the CRUD actions for Quiz model.
 */
class QuizController extends Controller
{
    /**
     * @param Webpage $webpage
     * @param int $subjectId
     * @return string
     */
    public function actionList($webpage, $subjectId = 0)
    {
        $subject = $subjectId ? Subject::findOne($subjectId) : null;
        return $this->render('list', [
            'webpage' => $webpage,
            'activeSubject' => $subject,
            'quizList' => Quiz::find()->with('subject')->orderBy('page_order')->all(),
            'quizResult' => new QuizResult(),
            'h1' => 'Проверь свой уровень' . ($subject ? " - \"{$subject->name}\"" : ''),
        ]);
    }

    /**
     * @param int $quizId
     * @return string
     * @throws yii\web\NotFoundHttpException
     */
    public function actionView($quizId = 0)
    {
        $quiz = null;
        if (!$quizId && \Yii::$app->request->isPost) {
            $quizId = \Yii::$app->request->post('quiz_id');
        }
        if ($quizId) $quiz = Quiz::findOne($quizId);
        if (!$quiz) throw new yii\web\NotFoundHttpException('Wrong request.');

        $quizResult = new QuizResult();
        if (Yii::$app->request->isPost) {
            if ($quizResult->load(Yii::$app->request->post())) {
                $quizResult->hash = sha1(strval(time()));
                $quizResult->subject_name = $quiz->subject->name;
                $quizResult->quiz_name = $quiz->name;
                $questionsData = [];
                $answersData = [];
                $indices = range(0, count($quiz->questions) - 1);
                shuffle($indices);
                for ($i = 0; $i < $quiz->questionCount; $i++) {
                    $answersData[] = -1;
                    $questionStruct = [
                        'question' => $quiz->questions[$indices[$i]]->content,
                        'answers' => [],
                    ];
                    foreach ($quiz->questions[$indices[$i]]->answers as $answer) $questionStruct['answers'][] = [$answer->content, $answer->is_right];
                    shuffle($questionStruct['answers']);
                    $questionsData[] = $questionStruct;
                }
                $quizResult->questions_data = json_encode($questionsData);
                $quizResult->answers_data = json_encode($answersData);
                if ($quizResult->save()) $this->redirect(yii\helpers\Url::to(['process', 'quizHash' => $quizResult->hash]));
            }
        }
        return $this->render('list', [
            'activeSubject' => $quiz->subject,
            'activeQuiz' => $quiz,
            'quizList' => Quiz::find()->with('subject')->orderBy('page_order')->all(),
            'quizResult' => $quizResult,
            'h1' => "Проверь свой уровень - \"{$quiz->subject->name}\"",
        ]);
    }

    /**
     * @param string $quizHash
     * @return string
     * @throws yii\web\NotFoundHttpException
     */
    public function actionProcess($quizHash)
    {
        $quizResult = QuizResult::findOne(['hash' => $quizHash]);
        if (!$quizResult) throw new yii\web\NotFoundHttpException('Wrong request');

        if (!$quizResult->timeLeft && !$this->completeQuiz($quizResult)) Error::logError('quiz:complete', $quizResult->getErrorsAsString());

        return $this->render('process', [
            'quizResult' => $quizResult,
        ]);
    }

    /**
     * @param string $quizHash
     * @return yii\web\Response
     */
    public function actionSaveResult($quizHash)
    {
        $jsonData = [];
        if (Yii::$app->request->isAjax) {
            $quizResult = QuizResult::findOne(['hash' => $quizHash]);
            if (!$quizResult) $jsonData = ['status' => 'error', 'message' => 'Wrong request'];
            else {
                if ($quizResult->finished_at) $jsonData = ['status' => 'error', 'message' => 'Тест уже был завершен'];
                elseif ($quizResult->timeLeft <= 0) $jsonData = ['status' => 'error', 'message' => 'Время для прохождения теста истекло.'];
                else {
                    $answersData = Yii::$app->request->post('answers', json_decode($quizResult->answers_data));
                    $quizResult->answers_data = json_encode($answersData);
                    if ($quizResult->save(true, ['answers_data'])) $jsonData = ['status' => 'ok', 'timeLeft' => $quizResult->timeLeft];
                    else {
                        $jsonData = ['status' => 'error', 'message' => 'Произошла ошибка сервера'];
                        Error::logError('quiz:saveResult', $quizResult->getErrorsAsString());
                    }
                }
            }
        }
        return $this->asJson($jsonData);
    }

    /**
     * @param string $quizHash
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionComplete($quizHash)
    {
        $jsonData = [];
        if (Yii::$app->request->isAjax) {
            $quizResult = QuizResult::findOne(['hash' => $quizHash]);
            if (!$quizResult) $jsonData = ['status' => 'error', 'message' => 'Wrong request'];
            else {
                if ($this->completeQuiz($quizResult)) {
                    $jsonData = [
                        'status' => 'ok',
                        'right_answers' => $quizResult->rightAnswerCount,
                        'total_answers' => count($quizResult->answersArray),
                        'student_name' => $quizResult->student_name,
                    ];
                } else {
                    $jsonData = ['status' => 'error', 'message' => 'Произошла ошибка сервера'];
                    Error::logError('quiz:complete', $quizResult->getErrorsAsString());
                }
            }
        }
        return $this->asJson($jsonData);
    }

    /**
     * @param QuizResult $quizResult
     * @return bool
     * @throws \Exception
     */
    private function completeQuiz($quizResult)
    {
        $quizResult->finished_at = date('Y-m-d H:i:s');
        if ($quizResult->timeLeft <= 0) {
            $timeLimit = clone $quizResult->createDate;
            $timeLimit->add(new \DateInterval('PT' . Quiz::TEST_TIME . 'M'));
            $quizResult->finished_at = $timeLimit->format('Y-m-d H:i:s');
        }
        if (!$quizResult->save(true, ['finished_at'])) return false;
        else return true;
    }
}