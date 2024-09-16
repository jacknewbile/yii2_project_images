<?php

namespace frontend\controllers;

use frontend\models\Images;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

	public function actionIndex($uuid = null)
	{
		if ($uuid === null) {
			$maxRetries = 50; // Limit the number of retries to avoid infinite loops
			$retries = 0;

			do {
				$uuid = rand(1, 100); // Generate random ID within the range
				$image = Images::findOne(['source_uuid' => $uuid]);
				$retries++;

				// Break the loop if max retries are exceeded
				if ($retries > $maxRetries) {
					break;
				}
			} while (!empty($image)); // Keep looping until we find image
		}
		// Check if the image has already been liked or disliked
		$image = Images::findOne(['source_uuid' => $uuid]);
		if ($image && $image->rate !== null) {
			return $this->redirect(['index']); // Redirect to another image if liked or disliked
		}

		$imageUrl = "https://picsum.photos/id/{$uuid}/200/300";

		// Pass the image URL and ID to the view
		return $this->render('index', [
			'imageUrl' => $imageUrl,
			'uuid' => $uuid,
		]);
	}

	public function actionLike($uuid)
	{
		Yii::$app->response->format = Response::FORMAT_JSON;

		// Save the like to the database (rate = 1)
		$this->saveImageVote($uuid, '1');

		// Generate a new random image ID
		$maxRetries = 50; // Limit the number of retries to avoid infinite loops
		$retries = 0;
		do {
			$newUUId = rand(1, 100); // Generate random ID within the range
			$image = Images::findOne(['source_uuid' => $newUUId]);
			$retries++;

			// Break the loop if max retries are exceeded
			if ($retries > $maxRetries) {
				break;
			}
		} while (!empty($image)); // Keep looping until we find image

		$imageUrl = "https://picsum.photos/id/{$newUUId}/200/300";
		// Return new image URL and ID as JSON
		return [
			'imageUrl' => $imageUrl,
			'newUUId' => $newUUId,
		];
	}

	public function actionDislike($uuid)
	{
		Yii::$app->response->format = Response::FORMAT_JSON;

		// Save the like to the database (rate = 1)
		$this->saveImageVote($uuid, '0');

		// Generate a new random image ID
		$maxRetries = 50; // Limit the number of retries to avoid infinite loops
		$retries = 0;
		do {
			$newUUId = rand(1, 100); // Generate random ID within the range
			$image = Images::findOne(['source_uuid' => $newUUId]);
			$retries++;

			// Break the loop if max retries are exceeded
			if ($retries > $maxRetries) {
				break;
			}
		} while (!empty($image)); // Keep looping until we find image

		$imageUrl = "https://picsum.photos/id/{$newUUId}/200/300";
		// Return new image URL and ID as JSON
		return [
			'imageUrl' => $imageUrl,
			'newUUId' => $newUUId,
		];
	}


    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }



    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

	protected function saveImageVote($uuid, $rate)
	{
		// Check if the image already exists in the database
		$image = Images::findOne(['source_uuid' => $uuid]);

		if (!$image) {
			// If the image doesn't exist, create a new record
			$image = new Images();
			$image->source_uuid = $uuid;
			$image->source_name = 'https://picsum.photos';
		}

		// Update the rate based on like or dislike
		$image->rate = $rate;
		$image->save();
	}
}
